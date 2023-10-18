<?php

namespace Control\Infrastructure\Models\ValueObjects;

use Assert\Assertion;

final class EmailAddress
{
    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        Assertion::email($value, "Not an email address.");

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getUsername(): string
    {
        return (explode('@', $this->value))[0];
    }

    public function getDomain(): string
    {
        return (explode('@', $this->value))[1];
    }
}
