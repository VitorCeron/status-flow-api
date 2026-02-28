<?php

namespace Tests\Feature\MonitorExecution;

use App\Domains\MonitorExecution\Services\MonitorExecutionService;
use App\Enums\MonitorIntervalEnum;
use App\Enums\MonitorStatusEnum;
use App\Jobs\ExecuteMonitorCheckJob;
use App\Mail\MonitorDownMail;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class MonitorExecutionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // -------------------------------------------------------------------------
    // getMonitorsDueToRun
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR EXECUTION] Should return active monitors never checked')]
    public function test_get_monitors_due_to_run_returns_never_checked_monitors(): void
    {
        // Arrange
        $activeMonitor   = Monitor::factory()->active()->create(['last_checked_at' => null]);
        $inactiveMonitor = Monitor::factory()->inactive()->create(['last_checked_at' => null]);

        $service = app(MonitorExecutionService::class);

        // Act
        $monitors = $service->getMonitorsDueToRun();

        // Assert
        $this->assertTrue($monitors->contains('id', $activeMonitor->id));
        $this->assertFalse($monitors->contains('id', $inactiveMonitor->id));
    }

    #[TestDox('[MONITOR EXECUTION] Should return monitors whose interval has elapsed')]
    public function test_get_monitors_due_to_run_returns_monitors_past_interval(): void
    {
        // Arrange — interval 60s, last checked 2 minutes ago → due
        $due = Monitor::factory()->active()->create([
            'interval'       => MonitorIntervalEnum::ONE_MINUTE->value,
            'last_checked_at'=> Carbon::now()->subMinutes(2),
        ]);

        // Arrange — interval 60s, last checked 10 seconds ago → NOT due
        $notDue = Monitor::factory()->active()->create([
            'interval'       => MonitorIntervalEnum::ONE_MINUTE->value,
            'last_checked_at'=> Carbon::now()->subSeconds(10),
        ]);

        $service = app(MonitorExecutionService::class);

        // Act
        $monitors = $service->getMonitorsDueToRun();

        // Assert
        $this->assertTrue($monitors->contains('id', $due->id));
        $this->assertFalse($monitors->contains('id', $notDue->id));
    }

    // -------------------------------------------------------------------------
    // executeCheck
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR EXECUTION] Should return UP status when response is 200')]
    public function test_execute_check_returns_up_status_for_200_response(): void
    {
        // Arrange
        $mock    = new MockHandler([new GuzzleResponse(200)]);
        $client  = new Client(['handler' => HandlerStack::create($mock)]);
        $this->app->instance(Client::class, $client);

        $monitor = Monitor::factory()->create(['url' => 'https://example.com', 'timeout' => 10]);
        $service = app(MonitorExecutionService::class);

        // Act
        $result = $service->executeCheck($monitor);

        // Assert
        $this->assertEquals(MonitorStatusEnum::UP->value, $result['status']);
        $this->assertEquals(200, $result['response_code']);
        $this->assertIsInt($result['response_time_ms']);
        $this->assertInstanceOf(Carbon::class, $result['checked_at']);
    }

    #[TestDox('[MONITOR EXECUTION] Should return DOWN status when response is non-200')]
    public function test_execute_check_returns_down_status_for_non_200_response(): void
    {
        // Arrange
        $mock   = new MockHandler([new GuzzleResponse(500)]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->app->instance(Client::class, $client);

        $monitor = Monitor::factory()->create(['url' => 'https://example.com', 'timeout' => 10]);
        $service = app(MonitorExecutionService::class);

        // Act
        $result = $service->executeCheck($monitor);

        // Assert
        $this->assertEquals(MonitorStatusEnum::DOWN->value, $result['status']);
        $this->assertEquals(500, $result['response_code']);
    }

    #[TestDox('[MONITOR EXECUTION] Should return DOWN status on connection failure')]
    public function test_execute_check_returns_down_on_connection_failure(): void
    {
        // Arrange
        $mock = new MockHandler([
            new ConnectException('Connection refused', new GuzzleRequest('GET', 'https://example.com')),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->app->instance(Client::class, $client);

        $monitor = Monitor::factory()->create(['url' => 'https://example.com', 'timeout' => 10]);
        $service = app(MonitorExecutionService::class);

        // Act
        $result = $service->executeCheck($monitor);

        // Assert
        $this->assertEquals(MonitorStatusEnum::DOWN->value, $result['status']);
        $this->assertNull($result['response_code']);
    }

    // -------------------------------------------------------------------------
    // processResult
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR EXECUTION] Should save log and update monitor status on UP result')]
    public function test_process_result_saves_log_and_updates_status_to_up(): void
    {
        // Arrange
        $monitor     = Monitor::factory()->create(['status' => MonitorStatusEnum::UNKNOWN->value]);
        $checkResult = [
            'status'           => MonitorStatusEnum::UP->value,
            'response_code'    => 200,
            'response_time_ms' => 100,
            'checked_at'       => Carbon::now(),
        ];
        $service = app(MonitorExecutionService::class);

        // Act
        $service->processResult($monitor, $checkResult);

        // Assert
        $this->assertDatabaseHas('monitor_logs', [
            'monitor_id' => $monitor->id,
            'status'     => MonitorStatusEnum::UP->value,
        ]);
        $this->assertDatabaseHas('monitors', [
            'id'     => $monitor->id,
            'status' => MonitorStatusEnum::UP->value,
        ]);
    }

    #[TestDox('[MONITOR EXECUTION] Should send email when fail threshold is reached')]
    public function test_process_result_sends_email_when_threshold_is_reached(): void
    {
        // Arrange
        Mail::fake();

        $monitor = Monitor::factory()->create([
            'status'         => MonitorStatusEnum::UNKNOWN->value,
            'fail_threshold' => 2,
        ]);

        // Create 1 previous DOWN log so the next DOWN crosses threshold of 2
        MonitorLog::factory()->down()->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subMinutes(1),
        ]);

        $checkResult = [
            'status'           => MonitorStatusEnum::DOWN->value,
            'response_code'    => null,
            'response_time_ms' => 5000,
            'checked_at'       => Carbon::now(),
        ];

        $service = app(MonitorExecutionService::class);

        // Act
        $service->processResult($monitor, $checkResult);

        // Assert
        Mail::assertSent(MonitorDownMail::class, fn ($mail) => $mail->hasTo($monitor->notify_email));
    }

    #[TestDox('[MONITOR EXECUTION] Should not send email when threshold is not yet reached')]
    public function test_process_result_does_not_send_email_below_threshold(): void
    {
        // Arrange
        Mail::fake();

        $monitor = Monitor::factory()->create([
            'status'         => MonitorStatusEnum::UNKNOWN->value,
            'fail_threshold' => 3,
        ]);

        $checkResult = [
            'status'           => MonitorStatusEnum::DOWN->value,
            'response_code'    => null,
            'response_time_ms' => 5000,
            'checked_at'       => Carbon::now(),
        ];

        $service = app(MonitorExecutionService::class);

        // Act
        $service->processResult($monitor, $checkResult);

        // Assert
        Mail::assertNothingSent();
    }

    #[TestDox('[MONITOR EXECUTION] Should not send email when monitor was already DOWN')]
    public function test_process_result_does_not_send_email_when_already_down(): void
    {
        // Arrange
        Mail::fake();

        $monitor = Monitor::factory()->down()->create([
            'fail_threshold' => 1,
        ]);

        // Already has enough failures
        MonitorLog::factory()->down()->count(2)->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subMinutes(2),
        ]);

        $checkResult = [
            'status'           => MonitorStatusEnum::DOWN->value,
            'response_code'    => null,
            'response_time_ms' => 5000,
            'checked_at'       => Carbon::now(),
        ];

        $service = app(MonitorExecutionService::class);

        // Act
        $service->processResult($monitor, $checkResult);

        // Assert — no email because status was already DOWN
        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // RunMonitorChecksCommand + Job dispatch
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR EXECUTION] Should dispatch one job per monitor due to run')]
    public function test_run_command_dispatches_jobs_for_due_monitors(): void
    {
        // Arrange
        Queue::fake();

        Monitor::factory()->active()->count(3)->create(['last_checked_at' => null]);
        Monitor::factory()->inactive()->count(2)->create(['last_checked_at' => null]);

        // Act
        $this->artisan('monitors:run')->assertSuccessful();

        // Assert
        Queue::assertPushed(ExecuteMonitorCheckJob::class, 3);
    }

    #[TestDox('[MONITOR EXECUTION] Should not dispatch jobs when no monitors are due')]
    public function test_run_command_dispatches_no_jobs_when_nothing_due(): void
    {
        // Arrange
        Queue::fake();

        Monitor::factory()->active()->create([
            'interval'       => MonitorIntervalEnum::ONE_MINUTE->value,
            'last_checked_at'=> Carbon::now()->subSeconds(10),
        ]);

        // Act
        $this->artisan('monitors:run')->assertSuccessful();

        // Assert
        Queue::assertNothingPushed();
    }

    // -------------------------------------------------------------------------
    // PruneMonitorLogsCommand
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR EXECUTION] Prune command should delete logs older than 90 days')]
    public function test_prune_command_deletes_old_logs(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create();

        MonitorLog::factory()->count(4)->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subDays(100),
        ]);
        MonitorLog::factory()->count(2)->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subDays(10),
        ]);

        // Act
        $this->artisan('monitors:prune-logs')->assertSuccessful();

        // Assert
        $this->assertDatabaseCount('monitor_logs', 2);
    }
}
