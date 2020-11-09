<?php

namespace Maxcelos\Foundation\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Prepare a JSON response for the given exception.
     *
     * This implementation fixes "Malformed UTF-8 characters" error
     * when using michaeldyrynda/laravel-efficient-uuid package
     *
     * @param Request   $request
     * @param Throwable $e
     *
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $exceptionArray = $this->convertExceptionToArray($e);

        $exceptionArray['message'] = mb_convert_encoding($exceptionArray['message'], 'ASCII');

        return new JsonResponse(
            $exceptionArray,
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
