<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class RocketChat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rocket-chat';
    }
}