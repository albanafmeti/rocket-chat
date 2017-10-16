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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
            return $response->body->messages;
        } else if ($response->code != 200) {
            throw new ChannelActionException($response->body->error);
        }

        throw new ChannelActionException($response->body->message);
    }
}