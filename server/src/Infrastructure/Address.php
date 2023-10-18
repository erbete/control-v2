<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'address',
        'city',
        'zip',
        'country',
        'account_owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(AccountOwner::class);
    }
}
