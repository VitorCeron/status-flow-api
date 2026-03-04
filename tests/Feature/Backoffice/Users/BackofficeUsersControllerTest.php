<?php

namespace Tests\Feature\Backoffice\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class BackofficeUsersControllerTest extends TestCase
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

    #[TestDox('[BACKOFFICE_USERS] Should return paginated response structure')]
    public function test_index_returns_paginated_structure(): void
    {
        // Arrange
        User::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/backoffice/users', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'timezone', 'role', 'created_at', 'deleted_at'],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta'  => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
        ]);
    }

    #[TestDox('[BACKOFFICE_USERS] Should default to per_page of 10')]
    public function test_index_defaults_per_page_to_10(): void
    {
        // Arrange
        User::factory()->count(12)->create();

        // Act
        $response = $this->getJson('/api/backoffice/users', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 10);
        $this->assertCount(10, $response->json('data'));
    }

    #[TestDox('[BACKOFFICE_USERS] Should respect custom per_page of 15')]
    public function test_index_respects_per_page_15(): void
    {
        // Arrange
        User::factory()->count(20)->create();

        // Act
        $response = $this->getJson('/api/backoffice/users?per_page=15', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 15);
    }

    #[TestDox('[BACKOFFICE_USERS] Should respect custom per_page of 50')]
    public function test_index_respects_per_page_50(): void
    {
        // Arrange
        User::factory()->count(5)->create();

        // Act
        $response = $this->getJson('/api/backoffice/users?per_page=50', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.per_page', 50);
    }

    #[TestDox('[BACKOFFICE_USERS] Should reject invalid per_page values')]
    public function test_index_rejects_invalid_per_page(): void
    {
        // Act
        $response = $this->getJson('/api/backoffice/users?per_page=25', $this->headers);

        // Assert
        $response->assertStatus(422);
    }

    #[TestDox('[BACKOFFICE_USERS] Should filter users by name search')]
    public function test_index_filters_by_name(): void
    {
        // Arrange
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        // Act
        $response = $this->getJson('/api/backoffice/users?search=John', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $this->assertEquals('John Doe', $response->json('data.0.name'));
    }

    #[TestDox('[BACKOFFICE_USERS] Should filter users by email search')]
    public function test_index_filters_by_email(): void
    {
        // Arrange
        User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@other.com']);

        // Act
        $response = $this->getJson('/api/backoffice/users?search=john%40example', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $this->assertEquals('john@example.com', $response->json('data.0.email'));
    }

    #[TestDox('[BACKOFFICE_USERS] Should return all users including soft-deleted when no filter is applied')]
    public function test_index_returns_all_users_by_default(): void
    {
        // Arrange
        $active  = User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        // Act
        $response = $this->getJson('/api/backoffice/users', $this->headers);

        // Assert
        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($active->id, $ids->toArray());
        $this->assertContains($deleted->id, $ids->toArray());
    }

    #[TestDox('[BACKOFFICE_USERS] Should exclude soft-deleted users when is_deleted is false')]
    public function test_index_excludes_deleted_users_when_is_deleted_false(): void
    {
        // Arrange
        $active  = User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        // Act
        $response = $this->getJson('/api/backoffice/users?is_deleted=false', $this->headers);

        // Assert
        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($active->id, $ids->toArray());
        $this->assertNotContains($deleted->id, $ids->toArray());
    }

    #[TestDox('[BACKOFFICE_USERS] Should return only soft-deleted users when is_deleted is true')]
    public function test_index_returns_only_deleted_users_when_is_deleted_true(): void
    {
        // Arrange
        User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        // Act
        $response = $this->getJson('/api/backoffice/users?is_deleted=true', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $this->assertEquals($deleted->id, $response->json('data.0.id'));
        $this->assertNotNull($response->json('data.0.deleted_at'));
    }

    #[TestDox('[BACKOFFICE_USERS] Should exclude admin users from results')]
    public function test_index_excludes_admin_users(): void
    {
        // Arrange
        $user = User::factory()->create();
        User::factory()->admin()->count(2)->create();

        // Act
        $response = $this->getJson('/api/backoffice/users', $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $this->assertEquals($user->id, $response->json('data.0.id'));
    }

    #[TestDox('[BACKOFFICE_USERS] Should fail without authentication')]
    public function test_index_fails_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/backoffice/users');

        // Assert
        $response->assertStatus(401);
    }

    #[TestDox('[BACKOFFICE_USERS] Should fail when authenticated as a regular user')]
    public function test_index_fails_for_non_admin_user(): void
    {
        // Arrange
        $user      = User::factory()->create(['password' => bcrypt($this->password)]);
        $userToken = $this->authenticate([
            'email'    => $user->email,
            'password' => $this->password,
        ]);

        // Act
        $response = $this->getJson('/api/backoffice/users', [
            'Authorization' => "Bearer {$userToken}",
        ]);

        // Assert
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    #[TestDox('[BACKOFFICE_USERS] Should return user details')]
    public function test_show_returns_user_details(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->getJson("/api/backoffice/users/{$user->id}", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'timezone', 'role', 'created_at', 'deleted_at'],
        ]);
        $response->assertJsonPath('data.id', $user->id);
    }

    #[TestDox('[BACKOFFICE_USERS] Should return soft-deleted user details')]
    public function test_show_returns_soft_deleted_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->delete();

        // Act
        $response = $this->getJson("/api/backoffice/users/{$user->id}", $this->headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $user->id);
        $this->assertNotNull($response->json('data.deleted_at'));
    }

    #[TestDox('[BACKOFFICE_USERS] Should return 404 when user does not exist')]
    public function test_show_returns_404_for_nonexistent_user(): void
    {
        // Act
        $response = $this->getJson('/api/backoffice/users/nonexistent-id', $this->headers);

        // Assert
        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    #[TestDox('[BACKOFFICE_USERS] Should return 404 when requesting an admin user')]
    public function test_show_returns_404_for_admin_user(): void
    {
        // Arrange
        $admin = User::factory()->admin()->create();

        // Act
        $response = $this->getJson("/api/backoffice/users/{$admin->id}", $this->headers);

        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('[BACKOFFICE_USERS] Should fail show without authentication')]
    public function test_show_fails_without_authentication(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->getJson("/api/backoffice/users/{$user->id}");

        // Assert
        $response->assertStatus(401);
    }
}
