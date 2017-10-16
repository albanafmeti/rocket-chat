<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\UserActionException;
use Noisim\RocketChat\Helpers\RocketChatRequest;
use Symfony\Component\HttpFoundation\Session\Session;

class Entity
{
    private $api_url;
    private $request;

    function __construct()
    {
        $this->api_url = config("rocket_chat.instance") . config("rocket_chat.api_root");
        $this->request = RocketChatRequest::singleton();
        $this->admin_login();
    }

    protected function api_url($path)
    {
        return rtrim($this->api_url, "/") . "/" . ltrim($path, "/");
    }

    protected function add_request_headers($headers)
    {
        RocketChatRequest::addHeaders($headers);
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

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
            $this->add_request_headers([
                'X-Auth-Token' => $response->body->data->authToken,
                'X-User-Id' => $response->body->data->userId,
            ]);

            $session->set('RC_X-Auth-Token', $response->body->data->authToken);
            $session->set('RC_X-User-Id', $response->body->data->userId);
            return true;
        }
        throw new UserActionException($response->body->message);
    }
}