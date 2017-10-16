<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class ChatSetting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chat-setting';
    }
}