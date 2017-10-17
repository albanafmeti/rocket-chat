<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class Chat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rc-chat';
    }
}