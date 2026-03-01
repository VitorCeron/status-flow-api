<?php

namespace Tests\Feature\Auth;

use App\Models\Monitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class DeleteAccountControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticationTrait;

    private User $user;
    private string $password = 'Password123!';
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['password' => Hash::make($this->password)]);
        $token = $this->authenticate([
            'email'    => $this->user->email,
            'password' => $this->password,
        ]);
        $this->headers = ['Authorization' => "Bearer {$token}"];
    }

    #[TestDox('[AUTH] Should delete account successfully')]
    public function test_delete_account_successfully(): void
    {
        // Arrange
        Monitor::factory()->count(2)->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->deleteJson('/api/auth/account', [], $this->headers);

        // Assert
        $response->assertStatus(204);
        $this->assertSoftDeleted('users', ['id' => $this->user->id]);
        $this->assertDatabaseCount('monitors', 2);
        $this->assertSoftDeleted('monitors', ['user_id' => $this->user->id]);
    }

    #[TestDox('[AUTH] Should fail to delete account without authentication')]
    public function test_delete_account_fails_without_authentication(): void
    {
        // Act
        $response = $this->deleteJson('/api/auth/account');

        // Assert
        $response->assertStatus(401);
    }

    #[TestDox('[AUTH] Should revoke all tokens when deleting account')]
    public function test_delete_account_revokes_all_tokens(): void
    {
        // Arrange
        $this->user->createToken('another_token');

        // Act
        $response = $this->deleteJson('/api/auth/account', [], $this->headers);

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
