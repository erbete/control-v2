<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $table = 'emails';

    protected $fillable = ['address', 'account_owner_id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(AccountOwner::class);
    }
}
