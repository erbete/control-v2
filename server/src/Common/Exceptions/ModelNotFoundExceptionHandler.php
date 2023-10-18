<?php

namespace Control\Common\Exceptions;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Control\Common\Traits\HttpErrorResponseTrait;

class ModelNotFoundExceptionHandler extends RecordsNotFoundException
{
    use HttpErrorResponseTrait;

    public function render(): JsonResponse
    {
        return $this->responseFailure(Response::HTTP_NOT_FOUND);
    }
}
