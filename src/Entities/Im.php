<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\ImActionException;

class Im extends Entity
{
    private $id;

    function __construct($id = null)
    {
        parent::__construct();
        $this->id = $id;
    }

    /* Removes the direct message from the user’s list of direct messages. */

    public function close($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ImActionException("Im ID not specified.");
        }

        $response = $this->request()->post($this->api_url("im.close"))
            ->body(['roomId' => $id])
            ->send();

        $this->handle_response($response, new ImActionException());
        return $this;
    }

    /* Retrieves the messages from a direct message. */
    public function history($id, $params = [])
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ImActionException("Im ID not specified.");
        }

        $extraQuery = http_build_query($params);

        $response = $this->request()->get($this->api_url("im.history") . "?roomId=$id&$extraQuery")->send();

        return $this->handle_response($response, new ImActionException(), ['messages']);
    }

    /* Lists all of the direct messages in the server, requires the permission 'view-room-administration' permission. */
    public function listEveryone()
    {
        $response = $this->request()->get($this->api_url("im.list.everyone"))->send();

        return $this->handle_response($response, new ImActionException(), ['ims']);
    }

    /* Lists all of the direct messages the calling user has joined. */
    public function listJoined()
    {
        $response = $this->request()->get($this->api_url("im.list"))->send();

        return $this->handle_response($response, new ImActionException(), ['ims']);
    }

    /* Retrieves the messages from any direct message in the server.
     * For this method to work the Enable Direct Message History Endpoint setting must be true, and the user calling this method must have the view-room-administration permission.
     */
    public function othersMessages($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ImActionException("Im ID not specified.");
        }

        $response = $this->request()->get($this->api_url("im.messages.others") . "?roomId=$id")->send();

        return $this->handle_response($response, new ImActionException(), ['messages']);
    }

    /* Adds the direct message back to the user’s list of direct messages. */
    public function open($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ImActionException("Im ID not specified.");
        }

        $response = $this->request()->post($this->api_url("im.open"))
            ->body(['roomId' => $id])
            ->send();

        $this->handle_response($response, new ImActionException());
        return $this;
    }

    /* Sets the topic for the direct message. */
    public function setTopic($topic, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ImActionException("Im ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.setTopic"))
            ->body(['roomId' => $id, 'topic' => $topic])
            ->send();

        return $this->handle_response($response, new ImActionException(), ['topic']);
    }
}