<?php

namespace Control\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Control\Rebinding\RebindingStatus;

class RebindingActivity extends Model
{
    use HasFactory;

    protected $table = 'rebinding_activities';

    protected $fillable = [
        'status',
        'note',
    ];

    protected function note(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => trim($value),
        );
    }

    public static $addNoteRules = [
        'note' => ['required', 'min:2', 'max:200'],
    ];

    public static $addNoteMessages = [
        'note.required' => 'Notat er obligatorisk.',
        'note.min' => 'Notatet må inneholde minst 2 tegn.',
        'note.max' => 'Notatet kan ikke være lenger enn 200 tegn.',
    ];

    public static function setStatusRules()
    {
        return [
            'status' => ['required', 'in:' . implode(',', array_column(RebindingStatus::cases(), 'value'))],
        ];
    }

    public static $setStatusMessages = [
        'status.required' => 'Status er obligatorisk.',
        'status.in' => 'Ugyldig rebindingsstatus',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
