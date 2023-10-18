<?php

namespace Control\CSVDumpProcessing\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Assert\Assertion;

use Control\Infrastructure\Models\ValueObjects\EmailAddress;

class EmailCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return new EmailAddress($attributes['email']);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        Assertion::isInstanceOf(
            $value,
            EmailAddress::class,
            'The given value is not an ' . EmailAddress::class,
            ' object.'
        );

        return [
            'email' => $value->getValue()
        ];
    }
}
