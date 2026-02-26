<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleEnum;
use App\Enums\TimezoneEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\AuthenticationTrait;
use PHPUnit\Framework\Attributes\TestDox;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticationTrait;

    private string $password = 'Password123!';
    private array $headers = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[TestDox('[AUTH] Should register user successfully')]
    public function test_register_successfully(): void
    {
        // Arrange
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->password,
            'password_confirmation' => $this->password,
            'timezone' => TimezoneEnum::AMERICA_SAO_PAULO->value,
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $payload);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'timezone', 'role', 'created_at'],
                'access_token',
                'token_type',
                'expires_at',
            ],
        ]);
        $this->assertDatabaseHas('users', ['email' => $payload['email']]);
    }

    #[TestDox('[AUTH] Should fail register with duplicate email')]
    public function test_register_fails_with_duplicate_email(): void
    {
        // Arrange
        $user = User::factory()->create();
        $payload = [
            'name' => $this->faker->name(),
            'email' => $user->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
            'timezone' => TimezoneEnum::UTC->value,
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[TestDox('[AUTH] Should fail register with invalid timezone')]
    public function test_register_fails_with_invalid_timezone(): void
    {
        // Arrange
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->password,
            'password_confirmation' => $this->password,
            'timezone' => 'Invalid/Timezone',
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['timezone']);
    }

    #[TestDox('[AUTH] Should fail register with missing required fields')]
    public function test_register_fails_with_missing_fields(): void
    {
        // Act
        $response = $this->postJson('/api/auth/register', []);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[TestDox('[AUTH] Should login successfully')]
    public function test_login_successfully(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt($this->password)]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $this->password,
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'timezone', 'role', 'created_at'],
                'access_token',
                'token_type',
                'expires_at',
            ],
        ]);
    }

    #[TestDox('[AUTH] Should fail login with wrong password')]
    public function test_login_fails_with_wrong_password(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt($this->password)]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);

        // Assert
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials.']);
    }

    #[TestDox('[AUTH] Should fail login with non-existent email')]
    public function test_login_fails_with_non_existent_email(): void
    {
        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'notfound@example.com',
            'password' => $this->password,
        ]);

        // Assert
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials.']);
    }

    #[TestDox('[AUTH] Should logout successfully')]
    public function test_logout_successfully(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt($this->password)]);
        $token = $this->authenticate(['email' => $user->email, 'password' => $this->password]);
        $headers = ['Authorization' => "Bearer {$token}"];

        // Act
        $response = $this->postJson('/api/auth/logout', [], $headers);

        // Assert
        $response->assertStatus(204);
    }

    #[TestDox('[AUTH] Should fail logout without authentication')]
    public function test_logout_fails_without_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/auth/logout');

        // Assert
        $response->assertStatus(401);
    }

    #[TestDox('[AUTH] Should return authenticated user data')]
    public function test_me_returns_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => bcrypt($this->password)]);
        $token = $this->authenticate(['email' => $user->email, 'password' => $this->password]);
        $headers = ['Authorization' => "Bearer {$token}"];

        // Act
        $response = $this->getJson('/api/auth/me', $headers);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'timezone', 'role', 'created_at'],
        ]);
        $response->assertJsonPath('data.email', $user->email);
    }

    #[TestDox('[AUTH] Should fail me without authentication')]
    public function test_me_fails_without_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/auth/me');

        // Assert
        $response->assertStatus(401);
    }

    #[TestDox('[AUTH] Should register user with default user role')]
    public function test_register_assigns_default_user_role(): void
    {
        // Arrange
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $payload);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonPath('data.user.role', RoleEnum::USER->value);
    }
}
