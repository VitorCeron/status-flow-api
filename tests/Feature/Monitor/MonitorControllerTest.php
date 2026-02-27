<?php

namespace Tests\Feature\Monitor;

use App\Enums\MonitorIntervalEnum;
use App\Enums\MonitorMethodEnum;
use App\Enums\MonitorStatusEnum;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class MonitorControllerTest extends TestCase
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
    // INDEX
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR] Should list only authenticated user monitors with pagination')]
    public function test_index_returns_only_own_monitors(): void
    {
        // Arrange
        Monitor::factory()->count(20)->create(['user_id' => $this->user->id]);
        Monitor::factory()->count(5)->create(); // other user's monitors

        // Act
        $response = $this->getJson('/api/monitors', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data'); // default per_page
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'user_id', 'name', 'url', 'method', 'interval',
                    'timeout', 'fail_threshold', 'notify_email', 'is_active',
                    'status', 'last_checked_at', 'created_at', 'updated_at',
                ],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta'  => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
        ]);
        $response->assertJsonPath('meta.total', 20);
        $response->assertJsonPath('meta.per_page', 15);
    }

    #[TestDox('[MONITOR] Should respect per_page query parameter')]
    public function test_index_respects_per_page_parameter(): void
    {
        // Arrange
        Monitor::factory()->count(10)->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/monitors?per_page=5', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('meta.total', 10);
        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonPath('meta.last_page', 2);
    }

    #[TestDox('[MONITOR] Should filter monitors by status')]
    public function test_index_filters_by_status(): void
    {
        // Arrange
        Monitor::factory()->count(5)->create(['user_id' => $this->user->id, 'status' => MonitorStatusEnum::UP->value]);
        Monitor::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => MonitorStatusEnum::DOWN->value]);

        // Act
        $response = $this->getJson('/api/monitors?status=up', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 5);
        foreach ($response->json('data') as $monitor) {
            $this->assertEquals(MonitorStatusEnum::UP->value, $monitor['status']);
        }
    }

    #[TestDox('[MONITOR] Should filter monitors by is_active')]
    public function test_index_filters_by_is_active(): void
    {
        // Arrange
        Monitor::factory()->count(7)->active()->create(['user_id' => $this->user->id]);
        Monitor::factory()->count(3)->inactive()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/monitors?is_active=false', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 3);
        foreach ($response->json('data') as $monitor) {
            $this->assertFalse($monitor['is_active']);
        }
    }

    #[TestDox('[MONITOR] Should filter monitors by method')]
    public function test_index_filters_by_method(): void
    {
        // Arrange
        Monitor::factory()->count(8)->create(['user_id' => $this->user->id, 'method' => MonitorMethodEnum::GET->value]);
        Monitor::factory()->count(2)->create(['user_id' => $this->user->id, 'method' => MonitorMethodEnum::POST->value]);

        // Act
        $response = $this->getJson('/api/monitors?method=POST', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 2);
        foreach ($response->json('data') as $monitor) {
            $this->assertEquals(MonitorMethodEnum::POST->value, $monitor['method']);
        }
    }

    #[TestDox('[MONITOR] Should reject invalid filter values')]
    public function test_index_rejects_invalid_filter_value(): void
    {
        // Act
        $response = $this->getJson('/api/monitors?status=invalid', $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    #[TestDox('[MONITOR] Should fail index without authentication')]
    public function test_index_fails_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/monitors');

        // Assert
        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR] Should create monitor successfully')]
    public function test_store_creates_monitor_successfully(): void
    {
        // Arrange
        $payload = [
            'name'           => 'My Website',
            'url'            => 'https://example.com',
            'method'         => MonitorMethodEnum::GET->value,
            'interval'       => MonitorIntervalEnum::ONE_MINUTE->value,
            'timeout'        => 10,
            'fail_threshold' => 3,
            'notify_email'   => 'alert@example.com',
        ];

        // Act
        $response = $this->postJson('/api/monitors', $payload, $this->headers);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'name', 'url', 'method', 'interval',
                'timeout', 'fail_threshold', 'notify_email', 'is_active',
                'status', 'last_checked_at', 'created_at', 'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.status', MonitorStatusEnum::UNKNOWN->value);
        $response->assertJsonPath('data.is_active', true);
        $response->assertJsonPath('data.user_id', $this->user->id);
        $this->assertDatabaseHas('monitors', ['name' => 'My Website', 'user_id' => $this->user->id]);
    }

    #[TestDox('[MONITOR] Should fail store with missing required fields')]
    public function test_store_fails_with_missing_fields(): void
    {
        // Act
        $response = $this->postJson('/api/monitors', [], $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'url', 'method', 'interval', 'timeout', 'fail_threshold', 'notify_email']);
    }

    #[TestDox('[MONITOR] Should fail store with invalid method enum')]
    public function test_store_fails_with_invalid_method(): void
    {
        // Arrange
        $payload = [
            'name'           => 'My Website',
            'url'            => 'https://example.com',
            'method'         => 'PATCH',
            'interval'       => MonitorIntervalEnum::ONE_MINUTE->value,
            'timeout'        => 10,
            'fail_threshold' => 3,
            'notify_email'   => 'alert@example.com',
        ];

        // Act
        $response = $this->postJson('/api/monitors', $payload, $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['method']);
    }

    #[TestDox('[MONITOR] Should fail store with invalid interval enum')]
    public function test_store_fails_with_invalid_interval(): void
    {
        // Arrange
        $payload = [
            'name'           => 'My Website',
            'url'            => 'https://example.com',
            'method'         => MonitorMethodEnum::GET->value,
            'interval'       => 999,
            'timeout'        => 10,
            'fail_threshold' => 3,
            'notify_email'   => 'alert@example.com',
        ];

        // Act
        $response = $this->postJson('/api/monitors', $payload, $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['interval']);
    }

    #[TestDox('[MONITOR] Should fail store without authentication')]
    public function test_store_fails_without_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/monitors', []);

        // Assert
        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // SHOW
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR] Should show own monitor successfully')]
    public function test_show_returns_own_monitor(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $monitor->id);
        $response->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'name', 'url', 'method', 'interval',
                'timeout', 'fail_threshold', 'notify_email', 'is_active',
                'status', 'last_checked_at', 'created_at', 'updated_at',
            ],
        ]);
    }

    #[TestDox('[MONITOR] Should return 404 for non-existent monitor')]
    public function test_show_returns_404_for_non_existent_monitor(): void
    {
        // Act
        $response = $this->getJson('/api/monitors/non-existent-id', $this->headers);

        // Assert
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Monitor not found.']);
    }

    #[TestDox('[MONITOR] Should return 403 when accessing another user monitor')]
    public function test_show_returns_403_for_other_user_monitor(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $monitor   = Monitor::factory()->create(['user_id' => $otherUser->id]);

        // Act
        $response = $this->getJson("/api/monitors/{$monitor->id}", $this->headers);

        // Assert
        $response->assertStatus(403);
        $response->assertJson(['message' => 'You are not authorized to access this monitor.']);
    }

    // -------------------------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR] Should update own monitor successfully')]
    public function test_update_own_monitor_successfully(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);
        $payload = ['name' => 'Updated Monitor Name', 'is_active' => false];

        // Act
        $response = $this->putJson("/api/monitors/{$monitor->id}", $payload, $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Updated Monitor Name');
        $response->assertJsonPath('data.is_active', false);
        $this->assertDatabaseHas('monitors', ['id' => $monitor->id, 'name' => 'Updated Monitor Name']);
    }

    #[TestDox('[MONITOR] Should return 403 when updating another user monitor')]
    public function test_update_returns_403_for_other_user_monitor(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $monitor   = Monitor::factory()->create(['user_id' => $otherUser->id]);

        // Act
        $response = $this->putJson("/api/monitors/{$monitor->id}", ['name' => 'Hack'], $this->headers);

        // Assert
        $response->assertStatus(403);
    }

    #[TestDox('[MONITOR] Should return 404 when updating non-existent monitor')]
    public function test_update_returns_404_for_non_existent_monitor(): void
    {
        // Act
        $response = $this->putJson('/api/monitors/non-existent-id', ['name' => 'Test'], $this->headers);

        // Assert
        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // DESTROY
    // -------------------------------------------------------------------------

    #[TestDox('[MONITOR] Should soft-delete own monitor successfully')]
    public function test_destroy_soft_deletes_own_monitor(): void
    {
        // Arrange
        $monitor = Monitor::factory()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->deleteJson("/api/monitors/{$monitor->id}", [], $this->headers);

        // Assert
        $response->assertStatus(204);
        $this->assertSoftDeleted('monitors', ['id' => $monitor->id]);
    }

    #[TestDox('[MONITOR] Should return 403 when deleting another user monitor')]
    public function test_destroy_returns_403_for_other_user_monitor(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $monitor   = Monitor::factory()->create(['user_id' => $otherUser->id]);

        // Act
        $response = $this->deleteJson("/api/monitors/{$monitor->id}", [], $this->headers);

        // Assert
        $response->assertStatus(403);
    }

    #[TestDox('[MONITOR] Should return 404 when deleting non-existent monitor')]
    public function test_destroy_returns_404_for_non_existent_monitor(): void
    {
        // Act
        $response = $this->deleteJson('/api/monitors/non-existent-id', [], $this->headers);

        // Assert
        $response->assertStatus(404);
    }
}
