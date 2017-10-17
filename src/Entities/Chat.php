<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\ChatActionException;

class Chat extends Entity
{
    public function postMessage($id, $paramType = "roomId", $params = [])
    {
        if (!in_array($paramType, ["roomId", "channel"])) {
            throw new ChatActionException("Bad method parameter value.");
        }

        $postData = [];
        foreach ($params as $field => $value) {
            $postData[$field] = $value;
        }
        $postData[$paramType] = $id;

        $response = $this->request()->post($this->api_url("chat.postMessage"))
            ->body($postData)
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body;
        } else if ($response->code != 200) {
            throw new ChatActionException($response->body->error);
        }

        throw new ChatActionException($response->body->message);
    }

    public function update($roomId, $msgId, $text)
    {
        $response = $this->request()->post($this->api_url("chat.update"))
            ->body([
                "roomId" => $roomId,
                "msgId" => $msgId,
                "text" => $text
            ])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->message;
        } else if ($response->code != 200) {
            throw new ChatActionException($response->body->error);
        }

        throw new ChatActionException($response->body->message);
    }

    public function delete($roomId, $msgId, $asUser = false)
    {
        $response = $this->request()->post($this->api_url("chat.delete"))
            ->body([
                "roomId" => $roomId,
                "msgId" => $msgId,
                "asUser" => $asUser
            ])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body;
        } else if ($response->code != 200) {
            throw new ChatActionException($response->body->error);
        }

        throw new ChatActionException($response->body->message);
    }
}