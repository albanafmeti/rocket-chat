<?php

namespace Noisim\RocketChat\Facades;

use Illuminate\Support\Facades\Facade;

class Im extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rc-im';
    }
}