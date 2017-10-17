<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\GroupActionException;

class Group extends Entity
{
    private $id;
    private $name;
    private $members = [];

    private $fillable = ["name", "members"];

    function __construct($group = null, $members = null)
    {
        parent::__construct();
        $this->create($group, $members);
    }

    public function create($group = null, $members = null)
    {
        if (is_array($group)) {
            foreach ($group as $field => $value) {
                $this->{$field} = $value;
            }
        } else {
            $this->name = ($group) ? $group : $this->name;
            $this->members = ($members) ? $members : $this->members;
        }
        return $this;
    }

    /* Creates a new private group, optionally including specified users. The group creator is always included. */
    public function store($group = null, $members = null)
    {
        $this->create($group, $members);
        $postData = [];
        foreach ($this->fillable as $field) {
            $postData[$field] = $this->{$field};
        }
        $response = $this->request()->post($this->api_url("groups.create"))
            ->body($postData)
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->group->_id;
            $this->name = $response->body->group->name;
            $this->members = $response->body->group->usernames;
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Adds all of the users of the Rocket.Chat server to the group. */
    public function addAll($roomId, $activeUsersOnly = false)
    {
        $response = $this->request()->post($this->api_url("groups.addAll"))
            ->body(['roomId' => $roomId, 'activeUsersOnly' => $activeUsersOnly])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Gives the role of moderator for a user in the current group. */
    public function addModerator($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("groups.addModerator"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Gives the role of owner for a user in the currrent group. */
    public function addOwner($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("groups.addOwner"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Archives a group. */
    public function archive($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.archive"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Cleans up a group, removing messages from the provided time range. */
    public function cleanHistory($roomId, $latestDate, $oldestDate, $inclusive = false)
    {
        $response = $this->request()->post($this->api_url("groups.archive"))
            ->body([
                'roomId' => $roomId,
                'latest' => $latestDate,
                'oldest' => $oldestDate,
                'inclusive' => $inclusive,
            ])->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Removes the group from the user’s list of groups. */

    public function close($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.close"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Retrieves the integrations which the group has, requires the permission 'manage-integrations' */
    public function getIntegrations($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->get($this->api_url("groups.getIntegrations") . "?roomId=$id")->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->integrations;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Retrieves the messages from a group. */
    public function history($id, $params = [])
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $extraQuery = http_build_query($params);

        $response = $this->request()->get($this->api_url("groups.history") . "?roomId=$id&$extraQuery")->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->messages;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Retrieves the information about the group. */
    public function get($id = null, $paramType = "roomId")
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        if (!in_array($paramType, ["roomId", "roomName"])) {
            throw new GroupActionException("Bad method parameter value.");
        }

        $response = $this->request()->get($this->api_url("groups.info") . "?$paramType=$id")->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->group->_id;
            $this->name = $response->body->group->name;
            $this->members = $response->body->group->usernames;
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Adds a user to the group. */
    public function invite($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("groups.invite"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Removes a user from the group. */
    public function kick($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("groups.kick"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Causes the callee to be removed from the group. */
    public function leave($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.leave"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Lists all of the groups the calling user has joined. */
    public function listJoined()
    {
        $response = $this->request()->get($this->api_url("groups.list.joined"))->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->groups;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Lists all of the groups on the server. */
    public function all()
    {
        $response = $this->request()->get($this->api_url("groups.list"))->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->groups;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Adds the group back to the user’s list of groups. */
    public function open($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.open"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Removes the role of moderator from a user in the current group. */
    public function removeModerator($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("groups.removeModerator"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Removes the role of owner from a user in the currrent group. */
    public function removeOwner($roomId, $userId)
    {
        $response = $this->request()->post($this->api_url("groups.removeOwner"))
            ->body(['roomId' => $roomId, 'userId' => $userId])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Changes the name of the group. */
    public function rename($newName, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.rename"))
            ->body(['roomId' => $id, 'name' => $newName])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Changes the name of the group. */
    public function setDescription($description, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.setDescription"))
            ->body(['roomId' => $id, 'description' => $description])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->description;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Sets the code required to join the group. */
    public function setJoinCode($joinCode, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.setJoinCode"))
            ->body(['roomId' => $id, 'joinCode' => $joinCode])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Sets the code required to join the group. */
    public function setPurpose($purpose, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.setPurpose"))
            ->body(['roomId' => $id, 'purpose' => $purpose])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->purpose;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Sets whether the group is read only or not. */
    public function setReadOnly($readOnly, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.setReadOnly"))
            ->body(['roomId' => $id, 'readOnly' => $readOnly])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }


    /* Sets the topic for the group. */
    public function setTopic($topic, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.setTopic"))
            ->body(['roomId' => $id, 'topic' => $topic])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->topic;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Sets the type of room this group should be. */
    public function setType($type, $id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        if (!in_array($type, ["c", "p"])) {
            throw new GroupActionException("Bad method parameter value.");
        }

        $response = $this->request()->post($this->api_url("groups.setType"))
            ->body(['roomId' => $id, 'type' => $type])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->group;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }

    /* Unarchives a group. */
    public function unarchive($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new GroupActionException("Room ID not specified.");
        }

        $response = $this->request()->post($this->api_url("groups.unarchive"))
            ->body(['roomId' => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $this;
        } else if ($response->code != 200) {
            throw new GroupActionException($response->body->error);
        }

        throw new GroupActionException($response->body->message);
    }
}