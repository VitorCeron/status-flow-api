<?php

namespace Tests\Feature\Backoffice\Dashboard;

use App\Enums\MonitorStatusEnum;
use App\Enums\TimezoneEnum;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class BackofficeDashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticationTrait;

    private User $admin;
    private string $token;
    private array $headers;
    private string $password = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'password' => bcrypt($this->password),
        ]);

        $this->token = $this->authenticate([
            'email'    => $this->admin->email,
            'password' => $this->password,
        ]);

        $this->headers = ['Authorization' => "Bearer {$this->token}"];
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return correct response structure')]
    public function test_summary_returns_correct_structure(): void
    {
        // Arrange
        $user = User::factory()->create();
        Monitor::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_users',
                'total_monitors',
                'total_up',
                'total_down',
                'last_users' => [
                    '*' => ['id', 'name', 'email', 'timezone', 'created_at'],
                ],
                'timezones' => [
                    '*' => ['timezone', 'total'],
                ],
            ],
        ]);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return correct total_users count excluding admins')]
    public function test_summary_counts_only_non_admin_users(): void
    {
        // Arrange
        User::factory()->count(3)->create();
        User::factory()->admin()->count(2)->create();

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_users', 3);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return correct total_monitors count across all users')]
    public function test_summary_counts_all_monitors(): void
    {
        // Arrange
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Monitor::factory()->count(3)->create(['user_id' => $userA->id]);
        Monitor::factory()->count(2)->create(['user_id' => $userB->id]);

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_monitors', 5);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return correct total_up and total_down counts')]
    public function test_summary_counts_up_and_down_monitors(): void
    {
        // Arrange
        $user = User::factory()->create();
        Monitor::factory()->count(4)->create([
            'user_id' => $user->id,
            'status'  => MonitorStatusEnum::UP->value,
        ]);
        Monitor::factory()->count(2)->create([
            'user_id' => $user->id,
            'status'  => MonitorStatusEnum::DOWN->value,
        ]);

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_up', 4);
        $response->assertJsonPath('data.total_down', 2);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return at most 5 users in last_users')]
    public function test_summary_limits_last_users_to_five(): void
    {
        // Arrange
        User::factory()->count(10)->create();

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.last_users'));
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return last_users ordered by created_at descending')]
    public function test_summary_last_users_ordered_by_created_at_desc(): void
    {
        // Arrange
        $oldest = User::factory()->create(['created_at' => now()->subDays(3)]);
        $newest = User::factory()->create(['created_at' => now()]);
        $middle = User::factory()->create(['created_at' => now()->subDay()]);

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $lastUsers = $response->json('data.last_users');
        $this->assertEquals($newest->id, $lastUsers[0]['id']);
        $this->assertEquals($middle->id, $lastUsers[1]['id']);
        $this->assertEquals($oldest->id, $lastUsers[2]['id']);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should exclude admins from last_users')]
    public function test_summary_last_users_excludes_admins(): void
    {
        // Arrange
        User::factory()->count(2)->create();
        User::factory()->admin()->count(3)->create();

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.last_users'));
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return correct timezone groupings')]
    public function test_summary_timezones_grouped_correctly(): void
    {
        // Arrange
        User::factory()->count(3)->create(['timezone' => TimezoneEnum::UTC->value]);
        User::factory()->count(2)->create(['timezone' => TimezoneEnum::AMERICA_SAO_PAULO->value]);

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $timezones = collect($response->json('data.timezones'))->keyBy('timezone');
        $this->assertEquals(3, $timezones->get(TimezoneEnum::UTC->value)['total']);
        $this->assertEquals(2, $timezones->get(TimezoneEnum::AMERICA_SAO_PAULO->value)['total']);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should return zeros and empty arrays when no data exists')]
    public function test_summary_returns_zeros_when_no_data(): void
    {
        // Act
        $response = $this->getJson('/api/backoffice/dashboard', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.total_users', 0);
        $response->assertJsonPath('data.total_monitors', 0);
        $response->assertJsonPath('data.total_up', 0);
        $response->assertJsonPath('data.total_down', 0);
        $response->assertJsonPath('data.last_users', []);
        $response->assertJsonPath('data.timezones', []);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should fail without authentication')]
    public function test_summary_fails_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/backoffice/dashboard');

        // Assert
        $response->assertStatus(401);
    }

    #[TestDox('[BACKOFFICE_DASHBOARD] Should fail when authenticated as a regular user')]
    public function test_summary_fails_for_non_admin_user(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt($this->password)]);
        $userToken = $this->authenticate([
            'email'    => $user->email,
            'password' => $this->password,
        ]);

        // Act
        $response = $this->getJson('/api/backoffice/dashboard', [
            'Authorization' => "Bearer {$userToken}",
        ]);

        // Assert
        $response->assertStatus(403);
    }
}
