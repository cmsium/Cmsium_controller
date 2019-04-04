<?php

namespace App\Exceptions;

use \Exception;

class AuthException extends Exception {

    protected $message = "Wrong auth credentials!";

}