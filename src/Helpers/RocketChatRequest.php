<?php

namespace Noisim\RocketChat\Helpers;

use Httpful\Request;

class RocketChatRequest
{
    private static $request;

    public static function create()
    {
        self::$request = Request::init()->sendsJson()->expectsJson();
        Request::ini(self::$request);
        return self::$request;
    }

    public static function singleton()
    {
        if (self::$request) {
            return self::$request;
        }
        return self::create();
    }

    public static function addHeaders($headers)
    {
        foreach ($headers as $header => $value) {
            self::$request->addHeader($header, $value);
        }
        Request::ini(self::$request);
    }
}