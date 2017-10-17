<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class ChatIm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chat-im';
    }
}