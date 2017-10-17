<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\SettingActionException;

class Setting extends Entity
{
    private $id;
    private $value;

    /* Gets the setting for the provided id. */
    public function get($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new SettingActionException("Setting ID not specified.");
        }

        $response = $this->request()->get($this->api_url("settings/$id"))->send();

        $body = $this->handle_response($response, new SettingActionException());
        $this->id = $body->_id;
        $this->value = $body->value;
        return $this;
    }

    /* Updates the setting for the provided id. */
    public function update($id = null, $value = null)
    {
        $id = ($id) ? $id : $this->id;
        $value = ($value !== null) ? $value : $this->value;

        if (!$id) {
            throw new SettingActionException("Setting ID not specified.");
        }

        $response = $this->request()->post($this->api_url("settings/$id"))
            ->body(["value" => $value])
            ->send();

        $this->handle_response($response, new SettingActionException());
        $this->id = $id;
        $this->value = $value;
        return $this;
    }
}