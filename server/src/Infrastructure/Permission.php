<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public static $createRules = [
        'name' => ['required', 'min:2', 'max:50'],
        'slug' => ['required', 'min:2', 'max:50', 'unique:permissions,slug'],
        'description' => ['required', 'max:500'],
    ];

    public static function editRules(self $permission)
    {
        return [
            'name' => ['required', 'min:2', 'max:50'],
            'slug' => ['required', 'min:2', 'max:50', (request()->slug === $permission->slug) ? '' : 'unique:permissions,slug'],
            'description' => ['required', 'max:500'],
        ];
    }

    public static $createMessages = [
        'name.required' => 'Navn er obligatorisk.',
        'name.min' => 'Navn må inneholde minst 2 tegn.',
        'name.max' => 'Navn kan ikke være lenger enn 50 tegn.',

        'slug.required' => 'Slug er obligatorisk.',
        'slug.min' => 'En slug må inneholde minst 2 tegn.',
        'slug.max' => 'En slug kan ikke være lenger enn 50 tegn.',
        'slug.unique' => 'En samme slug er allerede registrert.',

        'description.required' => 'En beskrivelse er obligatorisk.',
        'description.max' => 'Beskrivelsen kan ikke være lenger enn 500 tegn.',
    ];

    public static $detachRules = [
        'permissionId' => ['required', 'exists:permissions,id'],
        'userId' => ['required', 'exists:users,id'],
    ];

    public static $detachMessages = [
        'permissionId.required' => 'En tilgangs-ID er obligatorisk.',
        'userId.required' => 'En bruker-ID er obligatorisk.',

        'permissionId.exists' => 'Den forespurte tilgangen kan ikke bli funnet.',
        'userId.exists' => 'Den forespurte brukeren kan ikke bli funnet.',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_permissions')->withTimestamps();
    }
}
