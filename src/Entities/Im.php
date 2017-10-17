<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\ImActionException;

class Im extends Entity
{
    private $id;
    private $name;
    private $members = [];

    private $fillable = ["name", "members"];

    function __construct($im = null, $members = null)
    {
        parent::__construct();
        $this->create($im, $members);
    }

    public function create($im = null, $members = null)
    {
        if (is_array($im)) {
            foreach ($im as $field => $value) {
                $this->{$field} = $value;
            }
        } else {
            $this->name = ($im) ? $im : $this->name;
            $this->members = ($members) ? $members : $this->members;
        }
        return $this;
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

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
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

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->messages;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
    }

    /* Lists all of the direct messages in the server, requires the permission 'view-room-administration' permission. */
    public function listEveryone()
    {
        $response = $this->request()->get($this->api_url("im.list.everyone"))->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->ims;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
    }

    /* Lists all of the direct messages the calling user has joined. */
    public function listJoined()
    {
        $response = $this->request()->get($this->api_url("im.list"))->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->ims;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
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

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->messages;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
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

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
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

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->topic;
        } else if ($response->code != 200) {
            throw new ImActionException($response->body->error);
        }

        throw new ImActionException($response->body->message);
    }
}