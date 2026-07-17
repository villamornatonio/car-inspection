<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    public function register(): void
    {
        // Intentionally left blank; using render for consistent JSON API envelopes
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($e instanceof ValidationException) {
                return $this->validationError($e->errors(), 'Validation Error');
            }

            if ($e instanceof ModelNotFoundException) {
                return $this->notFound('Resource not found');
            }

            if ($e instanceof NotFoundHttpException) {
                return $this->notFound('Endpoint not found');
            }

            if ($e instanceof AuthenticationException) {
                return $this->error('Unauthenticated', [], 401);
            }

            return $this->error($e->getMessage() ?: 'Server Error', [], 500);
        }

        return parent::render($request, $e);
    }
}
