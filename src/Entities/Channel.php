<?php

namespace Noisim\RocketChat\Entities;


use Noisim\RocketChat\Exceptions\ChannelActionException;

class Channel extends Entity
{
    private $id;
    private $name;
    private $members = [];

    private $fillable = ["name", "members"];

    function __construct($channel = null, $members = null)
    {
        parent::__construct();
        $this->create($channel, $members);
    }

    public function create($channel = null, $members = null)
    {
        if (is_array($channel)) {
            foreach ($channel as $field => $value) {
                $this->{$field} = $value;
            }
        } else {
            $this->name = ($channel) ? $channel : $this->name;
            $this->members = ($members) ? $members : $this->members;
        }
        return $this;
    }

    /* Creates a new public channel, optionally including specified users. The channel creator is always included. */
    public function store($channel = null, $members = null)
    {
        $this->create($channel, $members);
        $postData = [];
        foreach ($this->fillable as $field) {
            $postData[$field] = $this->{$field};
        }
        $response = $this->request()->post($this->api_url("channels.create"))
            ->body($postData)
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->channel->_id;
            $this->name = $response->body->channel->name;
            $this->members = $response->body->channel->usernames;
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Adds all of the users of the Rocket.Chat server to the channel. */
    public function addAll($roomId, $activeUsersOnly = false)
    {
        $response = $this->request()->post($this->api_url("channels.addAll"))
            ->body(['roomId' => $roomId, 'activeUsersOnly' => $activeUsersOnly])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Gives the role of moderator for a user in the current channel. */
    public function addModerator($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.addModerator"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Gives the role of owner for a user in the currrent channel. */
    public function addOwner($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.addOwner"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Archives a channel. */
    public function archive($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.archive"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Cleans up a channel, removing messages from the provided time range. */
    public function cleanHistory($roomId, $latestDate, $oldestDate, $inclusive = false)
    {
        $response = $this->request()->post($this->api_url("channels.archive"))
            ->body([
                'roomId' => $roomId,
                'latest' => $latestDate,
                'oldest' => $oldestDate,
                'inclusive' => $inclusive,
            ])->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Removes the channel from the user’s list of channels. */

    public function close($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.close"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Retrieves the integrations which the channel has, requires the permission 'manage-integrations' */
    public function getIntegrations($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->get($this->api_url("channels.getIntegrations") . "?roomId=$id")->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->integrations;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Retrieves the messages from a channel. */
    public function history($id, $params = [])
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $extraQuery = http_build_query($params);

        $response = $this->request()->get($this->api_url("channels.history") . "?roomId=$id&$extraQuery")->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->messages;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Retrieves the information about the channel. */
    public function get($id = null, $paramType = "roomId")
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        if (!in_array($paramType, ["roomId", "roomName"])) {
            throw new ChannelActionException("Bad method parameter value.");
        }

        $response = $this->request()->get($this->api_url("channels.info") . "?$paramType=$id")->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->channel->_id;
            $this->name = $response->body->channel->name;
            $this->members = $response->body->channel->usernames;
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Adds a user to the channel. */
    public function invite($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.invite"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Removes a user from the channel. */
    public function kick($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.kick"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Causes the callee to be removed from the channel. */
    public function leave($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.leave"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Lists all of the channels the calling user has joined. */
    public function listJoined()
    {
        $response = $this->request()->get($this->api_url("channels.list.joined"))->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channels;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Lists all of the channels on the server. */
    public function all()
    {
        $response = $this->request()->get($this->api_url("channels.list"))->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channels;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Adds the channel back to the user’s list of channels. */
    public function open($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.open"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Removes the role of moderator from a user in the current channel. */
    public function removeModerator($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.removeModerator"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Removes the role of owner from a user in the currrent channel. */
    public function removeOwner($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.removeOwner"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Changes the name of the channel. */
    public function rename($newName, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.rename"))
            ->body(['roomId' => $id, 'name' => $newName])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Changes the name of the channel. */
    public function setDescription($description, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.setDescription"))
            ->body(['roomId' => $id, 'description' => $description])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->description;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Sets the code required to join the channel. */
    public function setJoinCode($joinCode, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.setJoinCode"))
            ->body(['roomId' => $id, 'joinCode' => $joinCode])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Sets the code required to join the channel. */
    public function setPurpose($purpose, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.setPurpose"))
            ->body(['roomId' => $id, 'purpose' => $purpose])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->purpose;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Sets whether the channel is read only or not. */
    public function setReadOnly($readOnly, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.setReadOnly"))
            ->body(['roomId' => $id, 'readOnly' => $readOnly])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }


    /* Sets the topic for the channel. */
    public function setTopic($topic, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.setTopic"))
            ->body(['roomId' => $id, 'topic' => $topic])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->topic;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Sets the type of room this channel should be. */
    public function setType($type, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        if (!in_array($type, ["c", "p"])) {
            throw new ChannelActionException("Bad method parameter value.");
        }

        $response = $this->request()->post($this->api_url("channels.setType"))
            ->body(['roomId' => $id, 'type' => $type])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->channel;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }

    /* Unarchives a channel. */
    public function unarchive($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("channels.unarchive"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }
}