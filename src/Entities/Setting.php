<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\SettingActionException;

class Setting extends Entity
{
    private $id;
    private $value;

    public function get($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new SettingActionException("Setting ID not specified.");
        }

        $response = $this->request()->get($this->api_url("settings/$id"))->send();
        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->_id;
            $this->value = $response->body->value;
            return $this;
        } else if ($response->code != 200) {
            throw new SettingActionException($response->body->error);
        } else {
            throw new SettingActionException($response->body->message);
        }
    }

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
        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $id;
            $this->value = $value;
            return $this;
        } else if ($response->code != 200) {
            throw new SettingActionException("Unable to update the setting.");
        }
    }
}