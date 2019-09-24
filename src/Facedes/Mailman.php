<?php

namespace Rgergo67\LaravelMailman\Facades;

use Illuminate\Support\Facades\Facade;

class Mailman extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'mailman';
    }

}