<?php

namespace Tests\Feature\MonitorStats;

use App\Enums\MonitorStatusEnum;
use App\Models\Monitor;
use App\Models\MonitorLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class MonitorStatsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticationTrait;

    private User $user;
    private string $token;
    private array $headers;
    private string $password = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create(['password' => bcrypt($this->password)]);
        $this->token = $this->authenticate([
            'email'    => $this->user->email,
            'password' => $this->password,
        ]);
        $this->headers = ['Authorization' => "Bearer {$this->token}"];
    }

    // -------------------------------------------------------------------------
    // GET /api/monitors/{id}/stats
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR STATS] Should return stats with correct structure for a monitor with logs')]
    public function test_returns_stats_structure_for_monitor_with_logs(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        MonitorLog::factory()->up()->create([
            'monitor_id'       => $monitor->id,
            'response_time_ms' => 200,
            'checked_at'       => Carbon::now()->subHours(2),
        ]);
        MonitorLog::factory()->down()->create([
            'monitor_id'  => $monitor->id,
            'checked_at'  => Carbon::now()->subHours(4),
        ]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'response_time_chart',
                'checks_history' => [
                    '*' => ['id', 'status', 'response_code', 'response_time_ms', 'checked_at'],
                ],
                'status_timeline' => [
                    '*' => ['checked_at', 'status'],
                ],
                'uptime_percentage',
                'last_fail',
            ],
        ]);
    }

    #[TestDox('[MONITOR STATS] Should return correct uptime percentage')]
    public function test_returns_correct_uptime_percentage(): void
    {
        // Arrange — 3 UP, 1 DOWN → 75%
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        MonitorLog::factory()->up()->count(3)->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subHours(1),
        ]);
        MonitorLog::factory()->down()->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subHours(2),
        ]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.uptime_percentage', 75.0);
    }

    #[TestDox('[MONITOR STATS] Should return last_fail as the most recent DOWN log checked_at')]
    public function test_returns_last_fail_timestamp(): void
    {
        // Arrange
        $monitor  = Monitor::factory()->create(['user_id' => $this->user->id]);
        $failTime = Carbon::now()->subHours(3)->startOfSecond();

        MonitorLog::factory()->down()->create([
            'monitor_id' => $monitor->id,
            'checked_at' => $failTime,
        ]);
        MonitorLog::factory()->up()->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subHour(),
        ]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.last_fail'));
    }

    #[TestDox('[MONITOR STATS] Should return null last_fail when monitor has no DOWN logs')]
    public function test_returns_null_last_fail_when_no_failures(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        MonitorLog::factory()->up()->count(5)->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subHour(),
        ]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.last_fail', null);
    }

    #[TestDox('[MONITOR STATS] Should return empty collections and zero uptime when monitor has no logs')]
    public function test_returns_empty_stats_for_monitor_with_no_logs(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.response_time_chart', []);
        $response->assertJsonPath('data.checks_history', []);
        $response->assertJsonPath('data.status_timeline', []);
        $response->assertJsonPath('data.uptime_percentage', 0.0);
        $response->assertJsonPath('data.last_fail', null);
    }

    #[TestDox('[MONITOR STATS] Should limit checks_history to the last 5 records')]
    public function test_checks_history_is_limited_to_5_records(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        MonitorLog::factory()->up()->count(10)->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subHour(),
        ]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data.checks_history');
    }

    #[TestDox('[MONITOR STATS] Should exclude status_timeline entries older than 7 days')]
    public function test_status_timeline_excludes_logs_older_than_7_days(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        MonitorLog::factory()->up()->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subDays(10),
        ]);
        MonitorLog::factory()->up()->create([
            'monitor_id' => $monitor->id,
            'checked_at' => Carbon::now()->subDays(1),
        ]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.status_timeline');
    }

    #[TestDox('[MONITOR STATS] Should return 404 when monitor does not exist')]
    public function test_returns_404_for_unknown_monitor(): void
    {
        // Act
        $response = $this->getJson('/api/monitors/non-existent-id/stats', $this->headers);

        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('[MONITOR STATS] Should return 403 when monitor belongs to another user')]
    public function test_returns_403_for_another_users_monitor(): void
    {
        // Arrange
        $otherMonitor = Monitor::factory()->create();

        // Act
        $response = $this->getJson("/api/monitors/{$otherMonitor->id}/stats", $this->headers);

        // Assert
        $response->assertStatus(403);
    }

    #[TestDox('[MONITOR STATS] Should return 401 when unauthenticated')]
    public function test_returns_401_when_unauthenticated(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}/stats");

        // Assert
        $response->assertStatus(401);
    }
}
