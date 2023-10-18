<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'account_owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(AccountOwner::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function rebindingActivities(): HasMany
    {
        return $this->hasMany(RebindingActivity::class);
    }

    public function rebindedAccounts(): HasMany
    {
        return $this->hasMany(RebindingAccount::class);
    }
}
