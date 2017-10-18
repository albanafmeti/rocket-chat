<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\RocketChatActionException;

class RocketChat extends Entity
{
    public function version()
    {
        $response = $this->request()->get($this->api_url("info"))->send();
        return $response->body->info->version;
    }

    public function info()
    {
        $response = $this->request()->get($this->api_url("info"))->send();
        return $response->body->info;
    }

    public function _request($path, $method = "GET", $body = [])
    {
        if (!in_array($method, ["GET", "POST", "PUT", "PATCH", "DELETE"])) {
            throw new RocketChatActionException("Bad method parameter value.");
        }

        switch ($method) {
            case "GET":
                return $this->request()->get($this->api_url($path))->send();
                break;
            case "POST":
                return $this->request()->post($this->api_url($path))
                    ->body($body)
                    ->send();
                break;
            case "DELETE":
                return $this->request()->delete($this->api_url($path))->send();
                break;
            case "PUT":
                return $this->request()->put($this->api_url($path))
                    ->body($body)
                    ->send();
                break;
            case "PATCH":
                return $this->request()->patch($this->api_url($path))
                    ->body($body)
                    ->send();
                break;
        }
    }
}