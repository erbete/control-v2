<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class AccountOwner extends Model
{
    use HasFactory;

    protected $table = 'account_owners';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'date_of_birth',
        'company',
        'personal_id',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'account_owner_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'account_owner_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'account_owner_id');
    }
}
