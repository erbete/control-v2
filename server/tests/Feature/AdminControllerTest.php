<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

use Control\Infrastructure\User;
use Control\Infrastructure\Permission;
use Database\Seeders\TestDatabaseSeeder;

class AdminControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_register_user()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $rebindingPermission = Permission::where('slug', '=', 'rebinding')->first();
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $registerResponse = $this->postJson('/api/admin/users/register', [
            'name' => 'jon daae',
            'email' => 'jondaae@EPOST.no',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
            'permissions' => ['rebinding'],
        ]);
        $userId = $registerResponse->json()['id'];

        // Assert
        $registerResponse
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('id', $userId)
                    ->where('name', 'Jon Daae')
                    ->where('email', 'jondaae@epost.no')
                    ->where('blocked', false)
                    ->where('blockedAt', '')
                    ->has('createdAt')
                    ->has('updatedAt')
                    ->where('permissions.0.id', (string)$rebindingPermission->id)
                    ->where('permissions.0.name', $rebindingPermission->name)
                    ->where('permissions.0.slug', $rebindingPermission->slug)
                    ->where('permissions.0.description', $rebindingPermission->description)
                    ->has('permissions.0.createdAt')
                    ->has('permissions.0.updatedAt')
                    ->missing('password')
                    ->etc()
            );
    }

    public function test_register_user_validation()
    {
        // Arrange
        $endpoint = '/api/admin/users/register';
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        // Name
        $responseEmptyName = $this->postJson($endpoint, [
            'name' => '',
            'email' => 'abc@EPOST.no',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        $responseNameTooShort = $this->postJson($endpoint, [
            'name' => 'j',
            'email' => 'abc@EPOST.no',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        $responseNameTooLong = $this->postJson($endpoint, [
            'name' => str_repeat('A', 51),
            'email' => 'abc@EPOST.no',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        // Email
        $responseEmptyEmail = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => '',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        // email rfc and dns testing is disabled in testing
        // $responseInvalidEmail = $this->postJson($endpoint, [
        //     'name' => 'jon daae',
        //     'email' => 'invalid@email',
        //     'password' => 'Password1234!?=',
        //     'password_confirmation' => 'Password1234!?=',
        //     'roles' => ['admin'],
        // ]);

        $responseEmailTooLong = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => str_repeat('a', 50) . '@long.com',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        // Unique email validation
        $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'unique@email.com',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        $responseEmailUnique = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'unique@email.com',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
        ]);

        // Password
        $responseEmptyPassword = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $responseNotConfirmedPassword = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => 'Password123!?=',
            'password_confirmation' => 'pass word 123 !?=',
        ]);

        $responsePasswordNotString = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => 1234,
            'password_confirmation' => 1234,
        ]);

        $responsePasswordTooShort = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => 'Pwdpwdpwd1!',
            'password_confirmation' => 'Pwdpwdpwd1!',
        ]);

        $responsePasswordInvalid = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => 'pwdpwdpwdpwdpwd',
            'password_confirmation' => 'pwdpwdpwdpwdpwd',
        ]);

        // Permission Slug
        $responseNotPermissionArray = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => 'Password123!?=',
            'password_confirmation' => 'Password123!?=',
            'permissions' => 'rebinding',
        ]);

        $responsePermissionNotExists = $this->postJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'test@email.com',
            'password' => 'Password123!?=',
            'password_confirmation' => 'Password123!?=',
            'permissions' => ['rebinding', 'unknown'],
        ]);

        // Assert
        // Name
        $responseEmptyName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('errors.0.name.0', 'Navn er obligatorisk.')
                    ->etc()
            );

        $responseNameTooShort
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.name.0', 'Navn må inneholde minst 2 tegn.')
                    ->etc()
            );

        $responseNameTooLong
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.name.0', 'Navn kan ikke være lenger enn 50 tegn.')
                    ->etc()
            );

        // Email
        $responseEmptyEmail
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-postadressen er obligatorisk.')
                    ->etc()
            );

        // email rfc and dns testing is disabled in testing
        // $responseInvalidEmail
        //     ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        //     ->assertJson(
        //         fn (AssertableJson $json) =>
        //         $json
        //             ->where('errors.0.email.0', 'E-postadressen må være en gyldig e-postadresse.')
        //             ->etc()
        //     );

        $responseEmailUnique
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-postadressen er allerede registrert.')
                    ->etc()
            );

        $responseEmailTooLong
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-postadressen kan ikke være lenger enn 50 tegn.')
                    ->etc()
            );

        // Password
        $responseEmptyPassword
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.password.0', 'Passord er obligatorisk.')
                    ->etc()
            );

        $responseNotConfirmedPassword
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.password.0', 'Passordbekreftelsen er ikke lik.')
                    ->etc()
            );

        $responsePasswordNotString
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.password.0', 'Ugyldig passord, velg et annet.')
                    ->etc()
            );

        $responsePasswordTooShort
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.password.0', 'Passord må inneholde minst 12 tegn.')
                    ->etc()
            );

        $responsePasswordInvalid
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.password.0', 'Passordkrav: minst 1 liten bokstav, 1 stor bokstav, 1 tall og 1 symbol.')
                    ->etc()
            );

        // Permission Slug
        $responseNotPermissionArray
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.permissions.0', 'Ugyldig tilgangsdata.')
                    ->etc()
            );

        $responsePermissionNotExists
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.permissions.0', "Den angitte tilgangen 'unknown' finnes ikke.")
                    ->etc()
            );
    }

    public function test_register_user_forbidden()
    {
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@user.no', 'password' => 'password']);

        // Act
        $response = $this->postJson('/api/admin/users/register', [
            'name' => 'jon daae',
            'email' => 'jondaae@EPOST.no',
            'password' => 'Password1234!?=',
            'password_confirmation' => 'Password1234!?=',
            'permissions' => ['admin'],
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('detail', 'Brukeren har ugyldig tilgang.')
                    ->etc()
            );
    }

    public function test_edit_user()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $user = User::where('email', '=', 'dev@user.no')->first();

        // Act
        $editResponse = $this->putJson('/api/admin/users/' . $user->id . '/edit', [
            'name' => 'new name',
            'email' => 'new@EPOST.no',
            'blocked' => false,
        ]);

        // Assert
        $editResponse
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('id', (string)$user->id)
                    ->where('name', 'New Name')
                    ->where('email', 'new@epost.no')
                    ->where('blocked', false)
                    ->where('blockedAt', '')
                    ->has('createdAt')
                    ->has('updatedAt')
                    ->has('permissions', 1)
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

    public function test_edit_user_forbidden()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@user.no', 'password' => 'password']);
        $user = User::where('email', '=', 'dev@admin.no')->first();

        // Act
        $response = $this->putJson('/api/admin/users/' . $user->id . '/edit', [
            'name' => 'new name',
            'email' => 'new@EPOST.no',
            'blocked' => false,
            'permissions' => ['admin'],
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('detail', 'Brukeren har ugyldig tilgang.')
                    ->etc()
            );
    }

    public function test_edit_user_validation()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $user = User::where('email', '=', 'dev@user.no')->first();
        $endpoint = '/api/admin/users/' . $user->id . '/edit';

        // Act
        $editResponseEmptyName = $this->putJson($endpoint, [
            'name' => '',
            'email' => 'new@EPOST.no',
            'blocked' => false,
        ]);

        $editResponseTooShortName = $this->putJson($endpoint, [
            'name' => 'a',
            'email' => 'new@EPOST.no',
            'blocked' => false,
        ]);

        $editResponseTooLongName = $this->putJson($endpoint, [
            'name' => str_repeat('a', 51),
            'email' => 'new@EPOST.no',
            'blocked' => false,
        ]);

        $editResponseEmptyEmail = $this->putJson($endpoint, [
            'name' => 'jon daae',
            'email' => '',
            'blocked' => false,
        ]);

        // $editResponseInvalidEmail = $this->putJson($endpoint, [
        //     'name' => 'jon daae',
        //     'email' => 'notvalid@email',
        //     'blocked' => false,
        // ]);

        $editResponseUniqueEmail = $this->putJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'dev@admin.no',
            'blocked' => false,
        ]);

        $editResponseTooLongEmail = $this->putJson($endpoint, [
            'name' => 'jon daae',
            'email' => str_repeat('a', 50) . '@epost.xyz',
            'blocked' => false,
        ]);

        // Blocked
        $editResponseEmptyBlocked = $this->putJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'new@EPOST.no',
            'blocked' => null,
        ]);

        $editResponseInvalidBlocked = $this->putJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'new@EPOST.no',
            'blocked' => 'not a boolean',
        ]);

        // Permission
        $editResponsePermissionNotExists = $this->putJson($endpoint, [
            'name' => 'jon daae',
            'email' => 'new@EPOST.no',
            'blocked' => false,
            'permissions' => ['rebundet', 'unknown'],
        ]);

        // Assert
        $editResponseEmptyName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.name.0', 'Navn er obligatorisk.')
                    ->etc()
            );

        $editResponseTooShortName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.name.0', 'Navn må inneholde minst 2 tegn.')
                    ->etc()
            );

        $editResponseTooLongName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.name.0', 'Navn kan ikke være lenger enn 50 tegn.')
                    ->etc()
            );

        $editResponseEmptyBlocked
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.blocked.0', 'Blokkert status er obligatorisk.')
                    ->etc()
            );

        $editResponseInvalidBlocked
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.blocked.0', 'Ugyldig blokkeringsstatus.')
                    ->etc()
            );

        $editResponseEmptyEmail
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-postadressen er obligatorisk.')
                    ->etc()
            );

        // $editResponseInvalidEmail
        //     ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        //     ->assertJson(
        //         fn (AssertableJson $json) =>
        //         $json
        //             ->where('errors.0.email.0', 'E-postadressen må være en gyldig e-postadresse.')
        //             ->etc()
        //     );

        $editResponseUniqueEmail
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-postadressen er allerede registrert.')
                    ->etc()
            );

        $editResponseTooLongEmail
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.email.0', 'E-postadressen kan ikke være lenger enn 50 tegn.')
                    ->etc()
            );

        $editResponsePermissionNotExists
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('errors.0.permissions.0', "Den angitte tilgangen 'unknown' finnes ikke.")
                    ->etc()
            );
    }

    public function test_get_user_by_id()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $user = User::where('email', '=', 'dev@user.no')->first();
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $userResponse = $this->getJson('/api/admin/users/' . $user->id);

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
                    ->missing('password')
                    ->etc()
            );
    }

    public function test_get_non_existing_user_by_id()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $userResponse = $this->getJson('/api/admin/users/123');

        // Assert
        $userResponse->assertNotFound()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('type', 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404')
                    ->where('title', 'Ressurs ikke funnet')
                    ->where('detail', 'Den forespurte ressursen kan ikke bli funnet.')
                    ->where('errors', [])
                    ->etc()
            );
    }

    public function test_get_users()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $user = User::where('email', '=', 'dev@admin.no')->first();

        // Act
        $usersResponse = $this->getJson('/api/admin/users');

        // Assert
        $usersResponse
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has(2));
    }

    public function test_get_permissions()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $response = $this->getJson('/api/admin/permissions');

        // Assert
        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has(2));
    }

    public function test_create_permission()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        // Act
        $response = $this->postJson('/api/admin/permissions/create', [
            'name' => 'Test',
            'slug' => 'test',
            'description' => 'Lorem Ipsum',
        ]);

        // Assert
        $response
            ->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->has('id')
                    ->where('name', 'Test')
                    ->where('slug', 'test')
                    ->where('description', 'Lorem Ipsum')
                    ->etc()
            );
    }

    public function test_create_permission_validation()
    {
        // Arrange
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $endpoint = '/api/admin/permissions/create';

        // Act
        // Name
        $responseEmptyName = $this->postJson($endpoint, [
            'name' => '',
            'slug' => 'test',
            'description' => 'Lorem Ipsum',
        ]);

        $responseTooShortName = $this->postJson($endpoint, [
            'name' => 'a',
            'slug' => 'test',
            'description' => 'Lorem Ipsum',
        ]);

        $responseTooLongName = $this->postJson($endpoint, [
            'name' => str_repeat('a', 51),
            'slug' => 'test',
            'description' => 'Lorem Ipsum',
        ]);

        // Slug
        $responseEmptySlug = $this->postJson($endpoint, [
            'name' => 'Test',
            'slug' => '',
            'description' => 'Lorem Ipsum',
        ]);

        $responseTooShortSlug = $this->postJson($endpoint, [
            'name' => 'Test',
            'slug' => 'a',
            'description' => 'Lorem Ipsum',
        ]);

        $responseTooLongSlug = $this->postJson($endpoint, [
            'name' => 'Test',
            'slug' => str_repeat('a', 51),
            'description' => 'Lorem Ipsum',
        ]);

        $responseNonUniqueSlug = $this->postJson($endpoint, [
            'name' => 'Test',
            'slug' => 'admin',
            'description' => 'Lorem Ipsum',
        ]);

        // Description
        $responseEmptyDescription = $this->postJson($endpoint, [
            'name' => 'Test',
            'slug' => 'test',
            'description' => '',
        ]);

        $responseTooLongDescription = $this->postJson($endpoint, [
            'name' => 'Test',
            'slug' => 'test',
            'description' => str_repeat('a', 501),
        ]);

        // Assert
        // Name
        $responseEmptyName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.name.0', 'Navn er obligatorisk.')
                    ->etc()
            );

        $responseTooShortName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.name.0', 'Navn må inneholde minst 2 tegn.')
                    ->etc()
            );

        $responseTooLongName
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.name.0', 'Navn kan ikke være lenger enn 50 tegn.')
                    ->etc()
            );

        // Slug
        $responseEmptySlug
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.slug.0', 'Slug er obligatorisk.')
                    ->etc()
            );

        $responseTooShortSlug
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.slug.0', 'En slug må inneholde minst 2 tegn.')
                    ->etc()
            );

        $responseTooLongSlug
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.slug.0', 'En slug kan ikke være lenger enn 50 tegn.')
                    ->etc()
            );

        $responseNonUniqueSlug
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.slug.0', 'En samme slug er allerede registrert.')
                    ->etc()
            );

        // Description
        $responseEmptyDescription
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.description.0', 'En beskrivelse er obligatorisk.')
                    ->etc()
            );

        $responseTooLongDescription
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.description.0', 'Beskrivelsen kan ikke være lenger enn 500 tegn.')
                    ->etc()
            );
    }

    public function test_edit_permission()
    {
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $permission = Permission::where('slug', '=', 'rebinding')->first();

        // Act
        $editResponse = $this->putJson('/api/admin/permissions/' . $permission->id . '/edit', [
            'name' => 'Test',
            'slug' => 'test',
            'description' => $permission->description,
        ]);

        // Assert
        $editResponse
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('id', (string)$permission->id)
                    ->where('name', 'Test')
                    ->where('slug', 'test')
                    ->where('description', $permission->description)
                    ->etc()
            );
    }

    public function test_delete_permission()
    {
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $testPermission = Permission::create([
            'name' => 'Test',
            'slug' => 'test',
            'description' => 'Lorem Ipsum',
        ]);

        // Act
        $deleteResponse = $this->deleteJson('/api/admin/permissions/' . $testPermission->id . '/delete');

        // Assert
        $deleteResponse->assertNoContent();
    }

    public function test_delete_assigned_permission()
    {
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $permission = Permission::where('slug', '=', 'rebinding')->first();

        // Act
        $deleteResponse = $this->deleteJson('/api/admin/permissions/' . $permission->id . '/delete');

        // Assert
        $deleteResponse->assertNoContent();
        $this->assertDatabaseMissing('permissions', [
            'slug' => $permission->slug,
        ]);
        $this->assertDatabaseCount('permissions', 1);
        $this->assertDatabaseMissing('users_permissions', [
            'permission_id' => $permission->id,
        ]);
    }

    public function test_detach_permission_from_user()
    {
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);

        $testUser = User::where('email', '=', 'dev@admin.no')->first();
        $testPermission = Permission::create([
            'name' => 'DetachTest',
            'slug' => 'detachtest',
            'description' => 'Lorem Ipsum',
        ]);
        $testPermission->users()->attach($testUser->id);

        // Act
        $this->assertDatabaseHas('permissions', [
            'slug' => 'detachtest',
        ]);
        $this->assertEquals(3, $testUser->permissions()->count());
        $detachResponse = $this->postJson('/api/admin/permissions/detach', [
            'permissionId' => $testPermission->id,
            'userId' => $testUser->id,
        ]);

        // Assert
        $detachResponse->assertNoContent();
        $this->assertDatabaseHas('permissions', [
            'slug' => 'detachtest',
        ]);
        $this->assertEquals(2, $testUser->permissions()->count());
    }

    public function test_detach_role_from_user_validation()
    {
        $this->seed(TestDatabaseSeeder::class);
        $this->postJson('/login', ['email' => 'dev@admin.no', 'password' => 'password']);
        $testUser = User::where('email', '=', 'dev@admin.no')->first();
        $testPermission = Permission::where('slug', '=', 'admin')->first();

        // Act
        $roleRequiredResponse = $this->postJson('/api/admin/permissions/detach', [
            'userId' => $testUser->id,
        ]);

        $roleDoesNotExistResponse = $this->postJson('/api/admin/permissions/detach', [
            'permissionId' => 123,
            'userId' => $testUser->id,
        ]);

        $userRequiredResponse = $this->postJson('/api/admin/permissions/detach', [
            'permissionId' => $testPermission->id,
        ]);

        $userDoesNotExistResponse = $this->postJson('/api/admin/permissions/detach', [
            'permissionId' => $testPermission->id,
            'userId' => 123,
        ]);

        $roleRequiredResponse
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.permissionId.0', 'En tilgangs-ID er obligatorisk.')
                    ->etc()
            );

        $roleDoesNotExistResponse
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.permissionId.0', 'Den forespurte tilgangen kan ikke bli funnet.')
                    ->etc()
            );

        $userRequiredResponse
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.userId.0', 'En bruker-ID er obligatorisk.')
                    ->etc()
            );

        $userDoesNotExistResponse
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json
                    ->where('title', 'Ugyldig data')
                    ->where('detail', 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.')
                    ->where('errors.0.userId.0', 'Den forespurte brukeren kan ikke bli funnet.')
                    ->etc()
            );
    }
}
