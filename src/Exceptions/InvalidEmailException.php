<?php

namespace Rgergo67\LaravelMailman\Exceptions;

use Exception;
use Throwable;

class InvalidEmailException extends Exception
{
    const MAILMAN_ERROR = "Cannot convert parameters: subscriber";
    /**
     * InvalidEmailException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(
        $message = "Invalid e-mail address format",
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
