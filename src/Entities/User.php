<?php

namespace Noisim\RocketChat\Entities;

use Noisim\RocketChat\Exceptions\UserActionException;
use Symfony\Component\HttpFoundation\Session\Session;

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

    public function login($auth_headers = false, $storeInSession = false)
    {
        $response = $this->request()->post($this->api_url("login"))
            ->body(['user' => $this->username, 'password' => $this->password])
            ->send();

        $data = $this->handle_response($response, new UserActionException(), ["data"]);
        $this->id = $data->userId;
        $this->authToken = $data->authToken;
        if ($auth_headers) {
            $this->add_request_headers([
                'X-Auth-Token' => $data->authToken,
                'X-User-Id' => $data->userId,
            ], $storeInSession);
        }
        return $this;
    }

    public function logout()
    {
        $response = $this->request()->get($this->api_url("logout"))->send();
        $message = $this->handle_response($response, new UserActionException(), ["data", "message"]);
        (new Session())->remove("RC_Headers");
        return $message;
    }

    public function me()
    {
        $response = $this->request()->get($this->api_url("me"))->send();
        $body = $this->handle_response($response, new UserActionException());
        $this->id = $body->_id;
        $this->name = $body->name;
        $this->email = $body->emails[0]->address;
        $this->emails = $body->emails;
        $this->username = $body->username;
        $this->active = $body->active;
        $this->roles = isset($body->roles) ? $body->roles : [];
        return $this;
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

        $user = $this->handle_response($response, new UserActionException(), ["user"]);
        $this->id = $user->_id;
        $this->name = $user->name;
        $this->email = $user->emails[0]->address;
        $this->emails = $user->emails;
        $this->username = $user->username;
        $this->active = $user->active;
        $this->roles = $user->roles;
        return $this;
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

        $user = $this->handle_response($response, new UserActionException(), ["user"]);
        $this->id = $user->_id;
        $this->name = $user->name;
        $this->email = $user->emails[0]->address;
        $this->emails = $user->emails;
        $this->username = $user->username;
        $this->active = $user->active;
        $this->roles = $user->roles;
        return $this;
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

        $response = $this->request()->get($this->api_url("users.info", [$paramType => $id]))->send();
        $user = $this->handle_response($response, new UserActionException(), ["user"]);
        $this->id = $user->_id;
        $this->name = $user->name;
        $this->email = $user->emails[0]->address;
        $this->emails = $user->emails;
        $this->username = $user->username;
        $this->active = $user->active;
        $this->roles = $user->roles;
        return $this;
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
        return $this->handle_response($response, new UserActionException());
    }

    public function all()
    {
        $response = $this->request()->get($this->api_url("users.list"))->send();
        return $this->handle_response($response, new UserActionException(), ["users"]);
    }
    
    public function getList($params = [])
    {
        $response = $this->request()->get($this->api_url("users.list", $params))->send();
        return $this->handle_response($response, new UserActionException());
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

        return $this->handle_response($response, new UserActionException(), ["data", "authToken"]);
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

        $response = $this->request()->get($this->api_url("users.getAvatar", [$paramType => $id]))
            ->send();
        return $this->handle_response($response, new UserActionException());
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

        $response = $this->request()->get($this->api_url("users.getPresence", [$paramType => $id]))
            ->send();
        return $this->handle_response($response, new UserActionException(), ["presence"]);
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

        $user = $this->handle_response($response, new UserActionException(), ["user"]);
        $this->id = $user->_id;
        $this->name = $user->name;
        $this->email = $user->emails[0]->address;
        $this->emails = $user->emails;
        $this->username = $user->username;
        $this->active = $user->active;
        $this->roles = $user->roles;
        return $this;
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
        return $this->handle_response($response, new UserActionException());
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

        return $this->handle_response($response, new UserActionException());
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
