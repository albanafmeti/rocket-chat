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

        $channel = $this->handle_response($response, new ChannelActionException(), ['channel']);
        $this->id = $channel->_id;
        $this->name = $channel->name;
        $this->members = $channel->usernames;
        return $this;
    }

    /* Adds all of the users of the Rocket.Chat server to the channel. */
    public function addAll($roomId, $activeUsersOnly = false)
    {
        $response = $this->request()->post($this->api_url("channels.addAll"))
            ->body(['roomId' => $roomId, 'activeUsersOnly' => $activeUsersOnly])
            ->send();

        return $this->handle_response($response, new ChannelActionException());
    }

    /* Gives the role of moderator for a user in the current channel. */
    public function addModerator($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.addModerator"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        $this->handle_response($response, new ChannelActionException());
        return $this;
    }

    /* Gives the role of owner for a user in the currrent channel. */
    public function addOwner($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.addOwner"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        $this->handle_response($response, new ChannelActionException());
        return $this;
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

        $this->handle_response($response, new ChannelActionException());
        return $this;
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

        $this->handle_response($response, new ChannelActionException());
        return $this;
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

        $this->handle_response($response, new ChannelActionException());
        return $this;
    }

    /* Retrieves the integrations which the channel has, requires the permission 'manage-integrations' */
    public function getIntegrations($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $response = $this->request()->get($this->api_url("channels.getIntegrations", ["roomId" => $id]))->send();

        return $this->handle_response($response, new ChannelActionException(), ['integrations']);
    }

    /* Retrieves the messages from a channel. */
    public function history($id, $params = [])
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new ChannelActionException("Room ID not specified.");
        }

        $params['roomId'] = $id;
        $response = $this->request()->get($this->api_url("channels.history", $params))->send();

        return $this->handle_response($response, new ChannelActionException(), ['messages']);
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

        $response = $this->request()->get($this->api_url("channels.info", [$paramType => $id]))->send();

        $channel = $this->handle_response($response, new ChannelActionException(), ['channel']);
        $this->id = $channel->_id;
        $this->name = $channel->name;
        $this->members = $channel->usernames;
        return $this;
    }

    /* Adds a user to the channel. */
    public function invite($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.invite"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
    }

    /* Removes a user from the channel. */
    public function kick($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.kick"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
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

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
    }

    /* Lists all of the channels the calling user has joined. */
    public function listJoined()
    {
        $response = $this->request()->get($this->api_url("channels.list.joined"))->send();

        return $this->handle_response($response, new ChannelActionException(), ['channels']);
    }

    /* Lists all of the channels on the server. */
    public function all()
    {
        $response = $this->request()->get($this->api_url("channels.list"))->send();

        return $this->handle_response($response, new ChannelActionException(), ['channels']);
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

        $this->handle_response($response, new ChannelActionException());
        return $this;
    }

    /* Removes the role of moderator from a user in the current channel. */
    public function removeModerator($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.removeModerator"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        $this->handle_response($response, new ChannelActionException());
        return $this;
    }

    /* Removes the role of owner from a user in the currrent channel. */
    public function removeOwner($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("channels.removeOwner"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        $this->handle_response($response, new ChannelActionException());
        return $this;
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

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
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

        return $this->handle_response($response, new ChannelActionException(), ['description']);
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

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
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

        return $this->handle_response($response, new ChannelActionException(), ['purpose']);
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

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
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

        return $this->handle_response($response, new ChannelActionException(), ['topic']);
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

        return $this->handle_response($response, new ChannelActionException(), ['channel']);
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

        $this->handle_response($response, new ChannelActionException(), ['channel']);
        return $this;
    }
}