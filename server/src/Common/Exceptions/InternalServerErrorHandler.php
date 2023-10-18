<?php

namespace Control\Common\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use Control\Common\Traits\HttpErrorResponseTrait;

class InternalServerErrorHandler extends HttpException
{
    use HttpErrorResponseTrait;

    public function render(): JsonResponse
    {
        return $this->responseFailure(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
