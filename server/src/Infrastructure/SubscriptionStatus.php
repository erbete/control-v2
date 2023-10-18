<?php

namespace Control\Infrastructure;

enum SubscriptionStatus: int
{
    case OUTPORTED = 410;
    case TERMINATED = 400;
    case OUTPORTING_PENDING = 310;
    case RESERVED = 240;
    case BLOCKED = 230;
    case ACTIVE = 200;
    case IMPORTING = 100;
    case TODO = 225; // what is this?
    case TODO2 = 280; // what is this?
    case TODO3 = 235; // what is this?

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
