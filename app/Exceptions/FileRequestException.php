<?php

namespace App\Exceptions;

use \Exception;

class FileRequestException extends Exception {

    protected $message = "Request to server was not sent!";

}