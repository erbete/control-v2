<?php

namespace Control\Rebinding\Actions;

class RebindingSetNoteOrStatusResult
{
    public function __construct(
        public readonly bool $accountExists,
        public readonly bool $success,
        public readonly bool $conflict,
    ) {
    }
}
