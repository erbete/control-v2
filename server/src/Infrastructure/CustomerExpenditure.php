<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CustomerExpenditure extends Model
{
    use HasFactory;

    protected $table = 'customer_expenditures';

    protected $fillable = [
        'usage',
        'source',
        'subscription_id',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
