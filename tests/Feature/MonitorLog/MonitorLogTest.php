<?php

namespace Tests\Feature\MonitorLog;

use App\Domains\MonitorLog\Services\MonitorLogService;
use App\Enums\MonitorStatusEnum;
use App\Models\Monitor;
use App\Models\MonitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class MonitorLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private MonitorLogService $service;
    private Monitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MonitorLogService::class);
        $this->monitor = Monitor::factory()->create();
    }

    // -------------------------------------------------------------------------
    // saveLog
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR LOG] Should save a UP check result log')]
    public function test_save_log_stores_up_result(): void
    {
        // Arrange
        $checkResult = [
            'status'           => MonitorStatusEnum::UP->value,
            'response_code'    => 200,
            'response_time_ms' => 250,
            'checked_at'       => Carbon::now(),
        ];

        // Act
        $log = $this->service->saveLog($this->monitor, $checkResult);

        // Assert
        $this->assertInstanceOf(MonitorLog::class, $log);
        $this->assertEquals($this->monitor->id, $log->monitor_id);
        $this->assertEquals(MonitorStatusEnum::UP, $log->status);
        $this->assertEquals(200, $log->response_code);
        $this->assertEquals(250, $log->response_time_ms);
        $this->assertDatabaseHas('monitor_logs', [
            'monitor_id'    => $this->monitor->id,
            'status'        => MonitorStatusEnum::UP->value,
            'response_code' => 200,
        ]);
    }

    #[TestDox('[MONITOR LOG] Should save a DOWN check result log with null response code')]
    public function test_save_log_stores_down_result_with_null_response_code(): void
    {
        // Arrange
        $checkResult = [
            'status'           => MonitorStatusEnum::DOWN->value,
            'response_code'    => null,
            'response_time_ms' => 5000,
            'checked_at'       => Carbon::now(),
        ];

        // Act
        $log = $this->service->saveLog($this->monitor, $checkResult);

        // Assert
        $this->assertEquals(MonitorStatusEnum::DOWN, $log->status);
        $this->assertNull($log->response_code);
        $this->assertDatabaseHas('monitor_logs', [
            'monitor_id'    => $this->monitor->id,
            'status'        => MonitorStatusEnum::DOWN->value,
            'response_code' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // countConsecutiveFailures
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR LOG] Should count consecutive DOWN logs correctly')]
    public function test_count_consecutive_failures_counts_only_recent_down_logs(): void
    {
        // Arrange — UP, DOWN, DOWN, DOWN (most recent first: 3 consecutive downs)
        MonitorLog::factory()->up()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(4)]);
        MonitorLog::factory()->down()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(3)]);
        MonitorLog::factory()->down()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(2)]);
        MonitorLog::factory()->down()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(1)]);

        // Act
        $count = $this->service->countConsecutiveFailures($this->monitor);

        // Assert
        $this->assertEquals(3, $count);
    }

    #[TestDox('[MONITOR LOG] Should stop counting consecutive failures at first UP')]
    public function test_count_consecutive_failures_stops_at_first_up(): void
    {
        // Arrange — DOWN, UP, DOWN (most recent first: 1 consecutive down)
        MonitorLog::factory()->down()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(3)]);
        MonitorLog::factory()->up()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(2)]);
        MonitorLog::factory()->down()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(1)]);

        // Act
        $count = $this->service->countConsecutiveFailures($this->monitor);

        // Assert
        $this->assertEquals(1, $count);
    }

    #[TestDox('[MONITOR LOG] Should return zero consecutive failures when latest log is UP')]
    public function test_count_consecutive_failures_returns_zero_when_latest_is_up(): void
    {
        // Arrange
        MonitorLog::factory()->down()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(2)]);
        MonitorLog::factory()->up()->create(['monitor_id' => $this->monitor->id, 'checked_at' => Carbon::now()->subMinutes(1)]);

        // Act
        $count = $this->service->countConsecutiveFailures($this->monitor);

        // Assert
        $this->assertEquals(0, $count);
    }

    #[TestDox('[MONITOR LOG] Should return zero consecutive failures when no logs exist')]
    public function test_count_consecutive_failures_returns_zero_when_no_logs(): void
    {
        // Act
        $count = $this->service->countConsecutiveFailures($this->monitor);

        // Assert
        $this->assertEquals(0, $count);
    }

    // -------------------------------------------------------------------------
    // deleteOlderThan
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR LOG] Should delete logs older than given days')]
    public function test_delete_older_than_removes_old_logs(): void
    {
        // Arrange
        MonitorLog::factory()->count(3)->create([
            'monitor_id' => $this->monitor->id,
            'checked_at' => Carbon::now()->subDays(100),
        ]);
        MonitorLog::factory()->count(2)->create([
            'monitor_id' => $this->monitor->id,
            'checked_at' => Carbon::now()->subDays(10),
        ]);

        // Act
        $deleted = $this->service->deleteOlderThan(90);

        // Assert
        $this->assertEquals(3, $deleted);
        $this->assertDatabaseCount('monitor_logs', 2);
    }

    #[TestDox('[MONITOR LOG] Should not delete recent logs')]
    public function test_delete_older_than_keeps_recent_logs(): void
    {
        // Arrange
        MonitorLog::factory()->count(5)->create([
            'monitor_id' => $this->monitor->id,
            'checked_at' => Carbon::now()->subDays(10),
        ]);

        // Act
        $deleted = $this->service->deleteOlderThan(90);

        // Assert
        $this->assertEquals(0, $deleted);
        $this->assertDatabaseCount('monitor_logs', 5);
    }
}
