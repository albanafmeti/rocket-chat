<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class ChatChannel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chat-channel';
    }
}