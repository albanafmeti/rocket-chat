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

        return $this->handle_response($response, new ChatActionException());
    }

    public function update($roomId, $msgId, $text)
    {
        $response = $this->request()->post($this->api_url("chat.update"))
            ->body([
                "roomId" => $roomId,
                "msgId" => $msgId,
                "text" => $text
            ])->send();

        return $this->handle_response($response, new ChatActionException(), ['message']);
    }

    public function delete($roomId, $msgId, $asUser = false)
    {
        $response = $this->request()->post($this->api_url("chat.delete"))
            ->body([
                "roomId" => $roomId,
                "msgId" => $msgId,
                "asUser" => $asUser
            ])->send();

        return $this->handle_response($response, new ChatActionException());
    }
}