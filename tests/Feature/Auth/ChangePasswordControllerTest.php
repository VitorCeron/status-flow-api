<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;

class ChangePasswordControllerTest extends TestCase
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

    #[TestDox('[AUTH] Should change password successfully')]
    public function test_change_password_successfully(): void
    {
        // Arrange
        $newPassword = 'NewPassword456!';
        $payload = [
            'old_password'          => $this->password,
            'password'              => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        // Act
        $response = $this->postJson('/api/auth/change-password', $payload, $this->headers);

        // Assert
        $response->assertStatus(204);
    }

    #[TestDox('[AUTH] Should fail change password without authentication')]
    public function test_change_password_fails_without_authentication(): void
    {
        // Arrange
        $payload = [
            'old_password'          => $this->password,
            'password'              => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ];

        // Act
        $response = $this->postJson('/api/auth/change-password', $payload);

        // Assert
        $response->assertStatus(401);
    }

    #[TestDox('[AUTH] Should fail change password with wrong old password')]
    public function test_change_password_fails_with_wrong_old_password(): void
    {
        // Arrange
        $payload = [
            'old_password'          => 'WrongPassword!',
            'password'              => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ];

        // Act
        $response = $this->postJson('/api/auth/change-password', $payload, $this->headers);

        // Assert
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials.']);
    }

    #[TestDox('[AUTH] Should fail change password with missing fields')]
    public function test_change_password_fails_with_missing_fields(): void
    {
        // Act
        $response = $this->postJson('/api/auth/change-password', [], $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['old_password', 'password']);
    }

    #[TestDox('[AUTH] Should fail change password when new password is too short')]
    public function test_change_password_fails_when_new_password_is_too_short(): void
    {
        // Arrange
        $payload = [
            'old_password'          => $this->password,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ];

        // Act
        $response = $this->postJson('/api/auth/change-password', $payload, $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    #[TestDox('[AUTH] Should fail change password when confirmation does not match')]
    public function test_change_password_fails_when_confirmation_does_not_match(): void
    {
        // Arrange
        $payload = [
            'old_password'          => $this->password,
            'password'              => 'NewPassword456!',
            'password_confirmation' => 'DoesNotMatch999!',
        ];

        // Act
        $response = $this->postJson('/api/auth/change-password', $payload, $this->headers);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
}
