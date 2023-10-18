<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;
use Closure;

/**
 *  Control user
 *  @property id INT GENERATED ALWAYS AS IDENTITY,
 *  @property name VARCHAR(255) NOT NULL,
 *  @property email VARCHAR(255) UNIQUE NOT NULL,
 *  @property password VARCHAR(255) NOT NULL,
 *  @property remember_token VARCHAR(100) NOT NULL,
 *  @property created_at TIMESTAMP,
 *  @property update_at TIMESTAMP,
 *  @property PRIMARY KEY(id)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'blocked',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Hash::make($value),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => mb_strtolower($value, 'UTF-8'),
        );
    }

    public static function loginRules()
    {
        $isTesting = App::environment() === 'testing' || App::environment() === 'dev';
        return [
            'email' => ['required', 'string', $isTesting ? 'email' : 'email:rfc,dns'],
            'password' => ['required', 'string']
        ];
    }

    public static $loginMessages = [
        'email.required' => 'E-post er obligatorisk.',
        'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
        'email.string' => 'E-postadressen må være en gyldig e-postadresse.',
        'password.required' => 'Passord er obligatorisk.',
        'password.string' => 'Passordet har et ugyldig format.',
    ];

    public static function registerRules()
    {
        $isTesting = App::environment() === 'testing' || App::environment() === 'dev';
        return [
            'name' => ['required', 'min:2', 'max:50'],
            'password' => ['required', 'confirmed', 'string', 'min:12', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'email' => ['required', ($isTesting ? 'email' : 'email:rfc,dns'), 'unique:users,email', 'max:50'],
            'permissions' => [function (string $attribute, mixed $requestedPermissions, Closure $fail) {
                if (!is_array($requestedPermissions)) {
                    $fail('Ugyldig tilgangsdata.');
                    return;
                }

                $availablePermissions = Permission::all()->pluck('slug');
                foreach ($requestedPermissions as $permission) {
                    if (!$availablePermissions->contains($permission)) {
                        $fail("Den angitte tilgangen '" . $permission . "' finnes ikke.");
                    }
                }
            }],
        ];
    }

    public static $registerMessages = [
        'name.required' => 'Navn er obligatorisk.',
        'name.min' => 'Navn må inneholde minst 2 tegn.',
        'name.max' => 'Navn kan ikke være lenger enn 50 tegn.',

        'email.required' => 'E-postadressen er obligatorisk.',
        'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
        'email.unique' => 'E-postadressen er allerede registrert.',
        'email.max' => 'E-postadressen kan ikke være lenger enn 50 tegn.',

        'password.required' => 'Passord er obligatorisk.',
        'password.confirmed' => 'Passordbekreftelsen er ikke lik.',
        'password.string' => 'Ugyldig passord, velg et annet.',
        'password.min' => 'Passord må inneholde minst 12 tegn.',
        'password.regex' => 'Passordkrav: minst 1 liten bokstav, 1 stor bokstav, 1 tall og 1 symbol.',
    ];

    public static function editRules(self $user)
    {
        $isTesting = App::environment() === 'testing' || App::environment() === 'dev';
        return [
            'name' => ['required', 'min:2', 'max:50'],
            'email' => ['required', ($isTesting ? 'email' : 'email:rfc,dns'), (request()->email === $user->email) ? '' : 'unique:users,email', 'max:50'],
            'blocked' => ['required', 'boolean'],
            'permissions' => [function (string $attribute, mixed $requestedPermissions, Closure $fail) {
                if (!is_array($requestedPermissions)) {
                    $fail('Ugyldig tilgangsdata.');
                    return;
                }

                $availablePermissions = Permission::all()->pluck('slug');
                foreach ($requestedPermissions as $permission) {
                    if (!$availablePermissions->contains($permission)) {
                        $fail("Den angitte tilgangen '" . $permission . "' finnes ikke.");
                    }
                }
            }],
        ];
    }

    public static $editMessages = [
        'name.required' => 'Navn er obligatorisk.',
        'name.min' => 'Navn må inneholde minst 2 tegn.',
        'name.max' => 'Navn kan ikke være lenger enn 50 tegn.',

        'blocked.required' => 'Blokkert status er obligatorisk.',
        'blocked.boolean' => 'Ugyldig blokkeringsstatus.',

        'email.required' => 'E-postadressen er obligatorisk.',
        'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
        'email.unique' => 'E-postadressen er allerede registrert.',
        'email.max' => 'E-postadressen kan ikke være lenger enn 50 tegn.',
    ];

    public function rebindingActivities(): HasMany
    {
        return $this->hasMany(RebindingActivity::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'users_permissions')->withTimestamps();
    }
}
