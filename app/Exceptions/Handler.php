<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     *
     * @throws \Exception
     *
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldReport($exception) && app()->bound('sentry')) {
            resolve('sentry')->captureException($exception);
        }

        parent::report($exception);
    }
}
