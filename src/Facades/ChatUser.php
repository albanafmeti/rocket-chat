<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class ChatUser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chat-user';
    }
}