<?php

namespace App\Auth;

/**
 * Class AuthGate
 */
class AuthGate {

    private $userToken;

    public function __construct() {
        // Check user token presence
        $userToken = app()->request->header['x-user-token'] ?? false;
        if (!$userToken) {
            throw new \App\Exceptions\AuthException();
        }

        $this->userToken = $userToken;
    }

    public function check() {
        // TODO: Implement
        return true;
    }

    public function getUserToken() {
        return $this->userToken;
    }

}