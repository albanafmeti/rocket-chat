<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\ChatActionException;
use Noisim\RocketChat\Exceptions\IntegrationActionException;

class Integration extends Entity
{
    /* Creates an integration, if the callee has the permission. */
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

    /* Removes an integration from the server. */
    public function remove($integrationId, $type = "webhook-outgoing")
    {
        $response = $this->request()->post($this->api_url("integrations.remove"))
            ->body([
                "integrationId" => $integrationId,
                "type" => $type
            ])->send();

        return $this->handle_response($response, new IntegrationActionException(), ['integration']);
    }

    /* Lists all of the integrations on the server. */
    public function all()
    {
        $response = $this->request()->get($this->api_url("integrations.list"))->send();
        return $this->handle_response($response, new IntegrationActionException());
    }
}