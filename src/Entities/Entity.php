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
    private $session;

    function __construct()
    {
        $this->session = new Session();
        $this->api_url = config("rocket_chat.instance") . config("rocket_chat.api_root");
        $this->request = RocketChatRequest::singleton();
        $this->main_login();
    }

    protected function api_url($path, $queryParams = [])
    {
        $path = rtrim($this->api_url, "/") . "/" . ltrim($path, "/");
        $query = array_merge($queryParams, $this->extraQuery);

        $url = count($query) ? $path . "?" . http_build_query($query) : $path;
        $this->extraQuery = [];
        return $url;
    }

    protected function add_request_headers($headers, $storeInSession = false)
    {
        if($storeInSession) {
            $this->session->set('RC_Headers', $headers);
        }
        return RocketChatRequest::add_headers($headers);
    }

    protected function request()
    {
        return $this->request;
    }

    public function main_login($useSession = true)
    {
        if ($useSession && $this->session->get('RC_Headers')) {
            $this->add_request_headers($this->session->get('RC_Headers'));
            return true;
        }

        if (config("rocket_chat.admin_username") && config("rocket_chat.admin_password")) {
            $response = $this->request()->post($this->api_url("login"))
                ->body([
                    'user' => config("rocket_chat.admin_username"),
                    'password' => config("rocket_chat.admin_password")
                ])
                ->send();

            $data = $this->handle_response($response, new UserActionException(), ['data']);

            $headers = [
                'X-Auth-Token' => $data->authToken,
                'X-User-Id' => $data->userId,
            ];
            $this->add_request_headers($headers, true);
            return true;
        }
        return false;
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
        if (count($fields) == 0) {
            return $body;
        } else if (count($fields) == 1) {
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
        return $body;
    }

    /* To use the next three methods the rest api method need to support the Offset and Count Query Parameters. */

    public function skip($value)
    {
        $this->extraQuery["offset"] = $value;
        return $this;
    }

    public function take($value)
    {
        $this->extraQuery["count"] = $value;
        return $this;
    }

    public function sort($value)
    {
        $value = is_array($value) ? json_encode((object)$value) : $value;
        $this->extraQuery["sort"] = $value;
        return $this;
    }
}
