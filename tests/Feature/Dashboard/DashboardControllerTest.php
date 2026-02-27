<?php

namespace Tests\Feature\Dashboard;

use App\Enums\MonitorStatusEnum;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class DashboardControllerTest extends TestCase
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
    // SUMMARY
    // -------------------------------------------------------------------------

    #[TestDox('[DASHBOARD] Should return dashboard summary for authenticated user')]
    public function test_summary_returns_correct_structure(): void
    {
        // Arrange
        Monitor::factory()->count(3)->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_monitors',
                'total_up',
                'total_down',
                'last_monitors' => [
                    '*' => ['id', 'name', 'url', 'is_up', 'status', 'created_at'],
                ],
            ],
        ]);
    }

    #[TestDox('[DASHBOARD] Should return correct total_monitors count')]
    public function test_summary_counts_only_own_monitors(): void
    {
        // Arrange
        Monitor::factory()->count(4)->create(['user_id' => $this->user->id]);
        Monitor::factory()->count(10)->create(); // other users' monitors

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_monitors', 4);
    }

    #[TestDox('[DASHBOARD] Should return correct total_up count')]
    public function test_summary_counts_only_up_monitors(): void
    {
        // Arrange
        Monitor::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => MonitorStatusEnum::UP->value]);
        Monitor::factory()->count(2)->create(['user_id' => $this->user->id, 'status' => MonitorStatusEnum::DOWN->value]);
        Monitor::factory()->count(1)->create(['user_id' => $this->user->id, 'status' => MonitorStatusEnum::UNKNOWN->value]);

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_up', 3);
    }

    #[TestDox('[DASHBOARD] Should return correct total_down count')]
    public function test_summary_counts_only_down_monitors(): void
    {
        // Arrange
        Monitor::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => MonitorStatusEnum::UP->value]);
        Monitor::factory()->count(2)->down()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_down', 2);
    }

    #[TestDox('[DASHBOARD] Should return at most 5 monitors in last_monitors')]
    public function test_summary_limits_last_monitors_to_five(): void
    {
        // Arrange
        Monitor::factory()->count(10)->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.last_monitors'));
    }

    #[TestDox('[DASHBOARD] Should return last_monitors ordered by created_at descending')]
    public function test_summary_last_monitors_are_ordered_by_created_at_desc(): void
    {
        // Arrange
        $oldest = Monitor::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDays(3)]);
        $newest = Monitor::factory()->create(['user_id' => $this->user->id, 'created_at' => now()]);
        $middle = Monitor::factory()->create(['user_id' => $this->user->id, 'created_at' => now()->subDay()]);

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $lastMonitors = $response->json('data.last_monitors');
        $this->assertEquals($newest->id, $lastMonitors[0]['id']);
        $this->assertEquals($middle->id, $lastMonitors[1]['id']);
        $this->assertEquals($oldest->id, $lastMonitors[2]['id']);
    }

    #[TestDox('[DASHBOARD] Should return only own monitors in last_monitors')]
    public function test_summary_last_monitors_excludes_other_user_monitors(): void
    {
        // Arrange
        Monitor::factory()->count(3)->create(['user_id' => $this->user->id]);
        Monitor::factory()->count(5)->create(); // other users

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.last_monitors'));
    }

    #[TestDox('[DASHBOARD] Should return is_up as a boolean in last_monitors')]
    public function test_summary_last_monitors_is_up_is_boolean(): void
    {
        // Arrange
        Monitor::factory()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $isUp = $response->json('data.last_monitors.0.is_up');
        $this->assertIsBool($isUp);
    }

    #[TestDox('[DASHBOARD] Should return empty counts and empty last_monitors when user has no monitors')]
    public function test_summary_returns_zeros_when_no_monitors(): void
    {
        // Act
        $response = $this->getJson('/api/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_monitors', 0);
        $response->assertJsonPath('data.total_up', 0);
        $response->assertJsonPath('data.total_down', 0);
        $response->assertJsonPath('data.last_monitors', []);
    }

    #[TestDox('[DASHBOARD] Should fail without authentication')]
    public function test_summary_fails_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/dashboard');

        // Assert
        $response->assertStatus(401);
    }
}
