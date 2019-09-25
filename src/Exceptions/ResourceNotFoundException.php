<?php

namespace Rgergo67\LaravelMailman\Exceptions;

use Exception;
use Throwable;

class ResourceNotFoundException extends Exception
{
    const MAILMAN_ERROR = "";
    const MAILMAN_CODE = 404;
    /**
     * InvalidEmailException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(
        $message = "Resource not found",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }
}
