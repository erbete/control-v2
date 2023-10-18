<?php

namespace Control\Common\Exceptions;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Control\Common\Traits\HttpErrorResponseTrait;

class ValidationExceptionHandler extends ValidationException
{
    use HttpErrorResponseTrait;

    public function render(): JsonResponse
    {
        return $this->responseFailure(
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            errors: $this->errors()
        );
    }
}
