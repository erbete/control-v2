<?php

namespace Control\Common\Traits;

class ProblemDetail
{
    public function __construct(
        public string $type,
        public string $title,
        public string $detail,
        public array $errors,
    ) {
    }
}
