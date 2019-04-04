<?php

namespace App\Utils;

/**
 * Class FileServerRequest
 */
class FileServerRequest {

    public $host;
    public $payload;

    public function __construct($host, $payload) {
        $this->host = trim($host, '/');
        $this->payload = $payload;
    }

    public function post($path) {
        // TODO: Get full path from config
        // TODO: Implement HTTP request with Swoole coroutines
    }

}