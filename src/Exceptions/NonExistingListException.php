<?php

namespace Rgergo67\LaravelMailman\Exceptions;

use Exception;
use Throwable;

class NonExistingListException extends Exception
{
    const MAILMAN_ERROR = "404 Not Found";
    /**
     * NonExistingListException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(
        $message = "List does not exist",
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
