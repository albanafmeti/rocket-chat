<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class ChatGroup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chat-group';
    }
}