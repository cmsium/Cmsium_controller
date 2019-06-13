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
        return true;
    }

    public function get($path) {
        // TODO: Get full path from config
        // TODO: Implement HTTP request with Swoole coroutines
        // Stub for test upload URL requests
        if ($path === 'url' && $this->host === 'file.service.local') {
            return [
                'id' => 1,
                'priority' => 0,
                'url' => 'http://file.server.local/'
            ];
        }
    }

}