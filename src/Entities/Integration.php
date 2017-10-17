<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\ChatActionException;
use Noisim\RocketChat\Exceptions\IntegrationActionException;

class Integration extends Entity
{
    public function create($params = [])
    {
        if (!in_array(array_keys($params), ["type", "name", "enabled", "username", "urls", "scriptEnabled"])) {
            throw new ChatActionException("Missing required parameter.");
        }

        $postData = [];
        foreach ($params as $field => $value) {
            $postData[$field] = $value;
        }

        $response = $this->request()->post($this->api_url("integrations.create"))
            ->body($postData)
            ->send();

        return $this->handle_response($response, new IntegrationActionException(), ['integration']);
    }
}