<?php

namespace Rgergo67\LaravelMailman\Exceptions;

use Exception;
use Throwable;

class EmailAlreadySubscribedException extends Exception
{
    const MAILMAN_ERROR = "Member already subscribed";

    /**
     * EmailAlreadySubscribedException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(
        $message = "E-mail address already subscribed",
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
