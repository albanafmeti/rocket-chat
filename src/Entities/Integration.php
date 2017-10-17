<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\ChatActionException;

class Integration extends Entity
{
    public function create($params = [])
    {
        if (!in_array(array_keys($params), ["type", "name", "enabled", "username", "urls", "scriptEnabled"])) {
            throw new ChatActionException("Missed required parameter.");
        }

        $postData = [];
        foreach ($params as $field => $value) {
            $postData[$field] = $value;
        }

        $response = $this->request()->post($this->api_url("integrations.create"))
            ->body($postData)
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->integration;
        } else if ($response->code != 200) {
            throw new ChatActionException($response->body->error);
        }

        throw new ChatActionException($response->body->message);
    }
}