<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class Group extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rc-group';
    }
}