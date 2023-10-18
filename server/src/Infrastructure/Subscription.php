<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    public $incrementing = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = [
        'id',
        'phone_number',
        'first_name',
        'last_name',
        'establish_date',
        'delivery_date',
        'lockin_end_date',
        'description',
        'status',
        'imsi',
        'account_id',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function customerExpenditures(): HasMany
    {
        return $this->hasMany(CustomerExpenditure::class);
    }
}
