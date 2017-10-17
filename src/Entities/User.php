<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\UserActionException;

class User extends Entity
{
    private $id;
    private $username;
    private $password;
    private $name;
    private $email;
    private $emails;

    private $roles = ['user'];
    private $active = true;

    private $fillable = ["username", "password", "name", "email", "roles", "active"];

    private $authToken;

    function __construct($user = null, $password = null, $name = null, $email = null)
    {
        parent::__construct();
        $this->create($user, $password, $name, $email);
    }

    public function create($user = null, $password = null, $name = null, $email = null)
    {
        if (is_array($user)) {
            foreach ($user as $field => $value) {
                $this->{$field} = $value;
            }
        } else {
            $this->username = ($user) ? $user : $this->username;
            $this->password = ($password) ? $password : $this->password;
            $this->name = ($name) ? $name : $this->name;
            $this->email = ($email) ? $email : $this->email;
        }
        return $this;
    }

    public function login($auth_headers = false)
    {
        $response = $this->request()->post($this->api_url("login"))
            ->body(['user' => $this->username, 'password' => $this->password])
            ->send();

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
            if ($auth_headers) {
                $this->add_request_headers([
                    'X-Auth-Token' => $response->body->data->authToken,
                    'X-User-Id' => $response->body->data->userId,
                ]);
            }
            $this->id = $response->body->data->userId;
            $this->authToken = $response->body->data->authToken;
            return $this;
        } else if ($response->code != 200 && isset($response->body->status) && $response->body->status == 'error') {
            throw new UserActionException($response->body->message);
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }

        throw new UserActionException($response->body->message);
    }

    public function logout()
    {
        $response = $this->request()->get($this->api_url("logout"))->send();

        if ($response->code == 200 && isset($response->body->status) && $response->body->status == 'success') {
            return true;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    public function me()
    {
        $response = $this->request()->get($this->api_url("me"))->send();
        if ($response->code == 200) {
            $this->id = $response->body->_id;
            $this->name = $response->body->name;
            $this->email = $response->body->emails[0]->address;
            $this->emails = $response->body->emails;
            $this->username = $response->body->username;
            $this->active = $response->body->active;
            $this->roles = isset($response->body->roles) ? $response->body->roles : [];
            return $this;
        } else if ($response->code != 200 && isset($response->body->status) && $response->body->status == 'error') {
            throw new UserActionException($response->body->message);
        }
    }

    public function store($user = null, $password = null, $name = null, $email = null)
    {
        $this->create($user, $password, $name, $email);
        $postData = [];
        foreach ($this->fillable as $field) {
            $postData[$field] = $this->{$field};
        }
        $response = $this->request()->post($this->api_url("users.create"))
            ->body($postData)
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->user->_id;
            $this->name = $response->body->user->name;
            $this->email = $response->body->user->emails[0]->address;
            $this->emails = $response->body->user->emails;
            $this->username = $response->body->user->username;
            $this->active = $response->body->user->active;
            $this->roles = $response->body->user->roles;
            return $this;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }

        throw new UserActionException($response->body->message);
    }

    public function update($fields = [])
    {
        $postData = [];
        $postData["userId"] = $this->id;
        foreach ($this->fillable as $field) {
            $postData["data"][$field] = $this->{$field};
        }
        foreach ($fields as $key => $value) {
            $postData["data"][$key] = $value;
        }

        if ($this->password == null && !isset($fields["password"])) {
            throw new UserActionException("Password is required when updating a user.");
        }

        $response = $this->request()->post($this->api_url("users.update"))
            ->body($postData)
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->user->_id;
            $this->name = $response->body->user->name;
            $this->email = $response->body->user->emails[0]->address;
            $this->emails = $response->body->user->emails;
            $this->username = $response->body->user->username;
            $this->active = $response->body->user->active;
            $this->roles = $response->body->user->roles;
            return $this;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }

        throw new UserActionException($response->body->message);
    }

    public function get($id = null, $paramType = "userId")
    {
        $id = ($id) ? $id : $this->id;

        if (!in_array($paramType, ["userId", "username"])) {
            throw new UserActionException("Bad method parameter value.");
        }

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->get($this->api_url("users.info?$paramType=$id"))->send();
        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->user->_id;
            $this->name = $response->body->user->name;
            $this->email = $response->body->user->emails[0]->address;
            $this->emails = $response->body->user->emails;
            $this->username = $response->body->user->username;
            $this->active = $response->body->user->active;
            $this->roles = $response->body->user->roles;
            return $this;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        } else {
            throw new UserActionException($response->body->message);
        }
    }

    public function delete($id = null)
    {
        $id = ($id) ? $id : $this->id;

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->post($this->api_url("users.delete"))
            ->body(["userId" => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return true;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    public function all()
    {
        $response = $this->request()->get($this->api_url('users.list'))->send();
        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body;
        } else {
            throw new UserActionException($response->body->message);
        }
    }

    public function createToken($id = null, $paramType = "userId")
    {
        $id = ($id) ? $id : $this->id;

        if (!in_array($paramType, ["userId", "username"])) {
            throw new UserActionException("Bad method parameter value.");
        }

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->post($this->api_url("users.createToken"))
            ->body([$paramType => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->authToken = $response->body->data->authToken;
            return $response->body->data->authToken;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    public function avatar($id = null, $paramType = "userId")
    {
        $id = ($id) ? $id : $this->id;
        if (!in_array($paramType, ["userId", "username"])) {
            throw new UserActionException("Bad method parameter value.");
        }

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->get($this->api_url("users.getAvatar") . "?$paramType=$id")
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    public function presence($id = null, $paramType = "userId")
    {
        $id = ($id) ? $id : $this->id;
        if (!in_array($paramType, ["userId", "username"])) {
            throw new UserActionException("Bad method parameter value.");
        }

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->get($this->api_url("users.getPresence") . "?$paramType=$id")
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return $response->body->presence;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    public function register($user = null, $password = null, $name = null, $email = null)
    {
        $this->create($user, $password, $name, $email);
        $postData = [];
        foreach ($this->fillable as $field) {
            $postData[$field] = $this->{$field};
        }
        $postData["pass"] = $this->password;
        unset($postData["password"]);

        $response = $this->request()->post($this->api_url("users.register"))
            ->body($postData)
            ->send();
        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            $this->id = $response->body->user->_id;
            $this->name = $response->body->user->name;
            $this->email = $response->body->user->emails[0]->address;
            $this->emails = $response->body->user->emails;
            $this->username = $response->body->user->username;
            $this->active = $response->body->user->active;
            $this->roles = $response->body->user->roles;
            return $this;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }

        throw new UserActionException($response->body->message);
    }

    public function resetAvatar($id = null, $paramType = "userId")
    {
        $id = ($id) ? $id : $this->id;

        if (!in_array($paramType, ["userId", "username"])) {
            throw new UserActionException("Bad method parameter value.");
        }

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->post($this->api_url("users.resetAvatar"))
            ->body([$paramType => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return true;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    public function setAvatar($avatarUrl, $id = null, $paramType = "userId")
    {
        $id = ($id) ? $id : $this->id;

        if (!in_array($paramType, ["userId", "username"])) {
            throw new UserActionException("Bad method parameter value.");
        }

        if (!$id) {
            throw new UserActionException("User ID not specified.");
        }

        $response = $this->request()->post($this->api_url("users.resetAvatar"))
            ->body(["avatarUrl" => $avatarUrl, $paramType => $id])
            ->send();

        if ($response->code == 200 && isset($response->body->success) && $response->body->success == true) {
            return true;
        } else if ($response->code != 200) {
            throw new UserActionException($response->body->error);
        }
    }

    /** Getters and Setters */

    public function id()
    {
        return $this->id;
    }

    public function username()
    {
        return $this->username;
    }

    public function email()
    {
        return $this->email;
    }

    public function emails()
    {
        return $this->emails;
    }

    public function name()
    {
        return $this->name;
    }

    public function roles()
    {
        return $this->roles;
    }

    public function authToken()
    {
        return $this->authToken;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }
}