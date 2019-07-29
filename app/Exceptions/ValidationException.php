<?php

namespace App\Exceptions;

use Exception;

/**
 * Class ValidationException
 */
class ValidationException extends Exception {

    use \Errors\Traits\ValidationException;

}