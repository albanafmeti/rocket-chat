<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\LivechatActionException;

class Livechat extends Entity
{

    /* Get a list of departments. */
    public function departments()
    {
        $response = $this->request()->get($this->api_url("livechat/department"))->send();
        return $this->handle_response($response, new LivechatActionException(), ['departments']);
    }

    /* Register a new department */
    public function addDepartment($postData)
    {
        $response = $this->request()->post($this->api_url("livechat/department"))
            ->body($postData)->send();

        return $this->handle_response($response, new LivechatActionException());
    }

    /* Get info about a department */
    public function department($id)
    {
        $response = $this->request()->get($this->api_url("livechat/department/$id"))->send();
        return $this->handle_response($response, new LivechatActionException());
    }

    /* Update a department */
    public function updateDepartment($id, $postData)
    {
        $response = $this->request()->put($this->api_url("livechat/department/$id"))
            ->body($postData)->send();

        return $this->handle_response($response, new LivechatActionException());
    }

    /* Removes a department */
    public function removeDepartment($id)
    {
        $response = $this->request()->delete($this->api_url("livechat/department/$id"))->send();
        return $this->handle_response($response, new LivechatActionException(), ['success']);
    }

    /* Save a SMS message on Rocket.Chat */
    public function smsIncoming($service, $postData)
    {
        $response = $this->request()->post($this->api_url("livechat/sms-incoming/$service"))
            ->body($postData)->send();

        return $this->handle_response($response, new LivechatActionException());
    }

    /* Get a list of agents or managers. */
    public function users($type = "agent")
    {
        $response = $this->request()->get($this->api_url("livechat/users/$type"))->send();
        return $this->handle_response($response, new LivechatActionException(), ['users']);
    }

    /* Register new agent or manager */
    public function addUser($type, $postData)
    {
        $response = $this->request()->post($this->api_url("livechat/users/$type"))
            ->body($postData)->send();

        return $this->handle_response($response, new LivechatActionException(), ['user']);
    }

    /* Get info about an agent or manager */
    public function user($id, $type = "agent")
    {
        $response = $this->request()->get($this->api_url("livechat/users/$type/$id"))->send();
        return $this->handle_response($response, new LivechatActionException(), ['user']);
    }

    /* Removes an agent or manager */
    public function removeUser($id, $type = "agent")
    {
        $response = $this->request()->delete($this->api_url("livechat/users/$type/$id"))->send();
        return $this->handle_response($response, new LivechatActionException(), ['success']);
    }
}