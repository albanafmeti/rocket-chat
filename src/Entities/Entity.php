<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\UserActionException;
use Noisim\RocketChat\Helpers\RocketChatRequest;
use Symfony\Component\HttpFoundation\Session\Session;

class Entity
{
    private $api_url;
    private $request;
    private $extraQuery = [];
    private $query = "";

    function __construct()
    {
        $this->api_url = config("rocket_chat.instance") . config("rocket_chat.api_root");
        $this->request = RocketChatRequest::singleton();
        $this->admin_login();
    }

    protected function api_url($path, $hasQuery = false)
    {
        $path = rtrim($this->api_url, "/") . "/" . ltrim($path, "/");
        if ($this->query()) {
            $url = ($hasQuery) ? $path . $this->query("&") : $path . $this->query("?");
        } else {
            $url = $path;
        }
        $this->extraQuery = [];
        $this->query = "";
        return $url;
    }

    public function query($symbol = "?")
    {
        return $this->query ? ($symbol . $this->query) : null;
    }

    protected function add_request_headers($headers)
    {
        RocketChatRequest::add_headers($headers);
    }

    protected function request()
    {
        return $this->request;
    }

    private function admin_login()
    {
        $session = new Session();
        if ($session->get('RC_X-Auth-Token') && $session->get('RC_X-User-Id')) {
            $this->add_request_headers([
                'X-Auth-Token' => $session->get('RC_X-Auth-Token'),
                'X-User-Id' => $session->get('RC_X-User-Id'),
            ]);
            return true;
        }

        $response = $this->request()->post($this->api_url("login"))
            ->body([
                'user' => config("rocket_chat.admin_username"),
                'password' => config("rocket_chat.admin_password")
            ])
            ->send();

        $data = $this->handle_response($response, new UserActionException(), ['data']);
        $this->add_request_headers([
            'X-Auth-Token' => $data->authToken,
            'X-User-Id' => $data->userId,
        ]);
        $session->set('RC_X-Auth-Token', $response->body->data->authToken);
        $session->set('RC_X-User-Id', $response->body->data->userId);
        return true;
    }

    public function handle_response($response, $exception, $fields = [])
    {
        $fields = is_string($fields) ? [$fields] : $fields;
        try {
            if ($response->code == 200) {

                if (isset($response->body->success) && $response->body->success == true) {
                    return $this->data($response->body, $fields);
                } else if (isset($response->body->status) && $response->body->status == 'success') {
                    return $this->data($response->body, $fields);
                } else if (isset($response->body->status) && $response->body->status == 'error') {
                    $exception->setMessage($response->body->message);
                } else if (isset($response->body->success) && $response->body->success == false) {
                    $exception->setMessage($response->body->error);
                } else {
                    $exception->setMessage("Something went wrong.");
                }

            } else {
                if (isset($response->body->status) && $response->body->status == 'error') {
                    $exception->setMessage($response->body->message);
                } else if (isset($response->body->success) && $response->body->success == false) {
                    $exception->setMessage($response->body->error);
                } else {
                    $exception->setMessage("Something went wrong.");
                }
            }

        } catch (\Exception $ex) {
            $exception->setMessage("Something went wrong.");
        }
        throw $exception;
    }

    private function data($body, $fields)
    {
        if (count($fields) == 1) {
            return isset($body->{$fields[0]}) ? $body->{$fields[0]} : $body;
        } else if (count($fields) == 2) {
            $stepOne = isset($body->{$fields[0]}) ? $body->{$fields[0]} : $body;
            $stepTwo = isset($stepOne->{$fields[1]}) ? $stepOne->{$fields[1]} : $stepOne;
            return $stepTwo;
        } else if (count($fields) == 3) {
            $stepOne = isset($body->{$fields[0]}) ? $body->{$fields[0]} : $body;
            $stepTwo = isset($stepOne->{$fields[1]}) ? $stepOne->{$fields[1]} : $stepOne;
            $stepThree = isset($stepTwo->{$fields[2]}) ? $stepTwo->{$fields[2]} : $stepTwo;
            return $stepThree;
        }
    }

    /* To use the next three methods the rest api method need to support the Offset and Count Query Parameters. */

    public function offset($value)
    {
        $this->extraQuery["offset"] = $value;
        $this->query = http_build_query($this->extraQuery);
        return $this;
    }

    public function count($value)
    {
        $this->extraQuery["count"] = $value;
        $this->query = http_build_query($this->extraQuery);
        return $this;
    }

    public function sort($value)
    {
        $value = is_array($value) ? json_encode((object)$value) : $value;
        $this->extraQuery["sort"] = $value;
        $this->query = http_build_query($this->extraQuery);
        return $this;
    }
}