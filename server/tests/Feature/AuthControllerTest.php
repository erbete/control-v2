<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

use Control\Infrastructure\User;
use Database\Seeders\TestDatabaseSeeder;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_login()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);

        // Act
        $response = $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Assert
        $response->assertNoContent();
    }

    public function test_login_validation()
    {
        // Act
        $responseInvalidEmail = $this->postJson('/login', ['email' => 'test@test', 'password' => 'password']);
        $responseEmptyEmail = $this->postJson('/login', ['email' => '', 'password' => 'password']);
        $responseEmptyPassword = $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => '']);

        // Assert
        $responseInvalidEmail
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('type', 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422')
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.email.0', 'Feil innloggingsdetaljer.')
                    ->etc()
            );

        $responseEmptyEmail
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-post er obligatorisk.')
                    ->etc()
            );

        $responseEmptyPassword
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.password.0', 'Passord er obligatorisk.')
                    ->etc()
            );
    }

    public function test_login_unauthorized()
    {
        // Act
        $responseWrongEmail = $this->postJson('/login', ['email' => 'wrong@email.com', 'password' => 'password']);
        $responseWrongPassword = $this->postJson('/login', ['email' => 'test@epost.no', 'password' => 'wronpwd123!']);

        // Assert
        $responseWrongEmail->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'Feil innloggingsdetaljer.')
                    ->etc()
            );

        $responseWrongPassword->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'Feil innloggingsdetaljer.')
                    ->etc()
            );
    }

    public function test_login_blocked()
    {
        $this->seed(TestDatabaseSeeder::class);

        User::create([
            'name' => 'im blocked',
            'email' => 'blocked@EPOST.xyz',
            'password' => 'password',
            'blocked' => true,
        ]);

        // Act
        $response = $this->postJson('/login', ['email' => 'blocked@epost.xyz', 'password' => 'password']);

        // Assert
        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('detail', 'Brukeren er sperret.')
                    ->etc()
            );
    }

    public function test_logout()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $response = $this->postJson('/logout', []);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_get_user()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $user = User::where('email', '=', 'dev@admin.no')->first();

        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $userResponse = $this->getJson('/api/auth/user');

        // Assert
        $userResponse
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('id', (string)$user->id)
                    ->where('name', $user->name)
                    ->where('email', $user->email)
                    ->where('blocked', $user->blocked)
                    ->where('blockedAt', '')
                    ->has('createdAt')
                    ->has('updatedAt')
                    ->where('permissions.0.id', (string)$user->permissions->first()->id)
                    ->where('permissions.0.name', $user->permissions->first()->name)
                    ->where('permissions.0.slug', $user->permissions->first()->slug)
                    ->where('permissions.0.description', $user->permissions->first()->description)
                    ->has('permissions.0.createdAt')
                    ->has('permissions.0.updatedAt')
                    ->missing('password')
                    ->etc()
            );
    }
}
