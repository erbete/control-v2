<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RebindingAccount extends Model
{
    use HasFactory;

    protected $table = 'rebinded_accounts';

    protected $fillable = ['data'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
