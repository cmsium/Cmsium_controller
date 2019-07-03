<?php

namespace App\Utils;

use App\Exceptions\FileRequestException;
use Swoole\Coroutine\Http\Client;

/**
 * Class AsyncFileServerRequest
 */
class FileServerRequest {

    public $host;
    public $payload;
    public $response;
    public $async = false;
    /**
     * @var Client
     */
    private $client;
    private $httpPort = 80;

    public function __construct($url, $payload = null) {
        $this->host = parse_url($url, PHP_URL_HOST);
        $this->httpPort = parse_url($url, PHP_URL_PORT);
        $this->payload = $payload;

        // Set connector
        $this->client = new Client($this->host, $this->httpPort);
        $this->client->setHeaders([
            'Host' => $this->host,
            'User-Agent' => 'CmsiumFileController/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml,application/json'
        ]);
        $this->client->set([ 'timeout' => 1]);
        if ($this->async) { $this->client->setDefer(); }
    }

    public function post($path) {
        if (!$this->client) {
            throw new FileRequestException('Could not connect using HTTP client!');
        }

        $path = '/'.trim($path, '/');
        $status = $this->client->post($path, $this->payload);

        // If async, return status, not response
        if ($this->async) {
            return $status;
        }

        if ($this->client->statusCode != 200) {
            throw new FileRequestException("Server answered with {$this->client->statusCode} status code!");
        }
        $result = $this->bodyAsArray();
        $this->client->close();
        return $result;
    }

    public function get($path) {
        if (!$this->client) {
            throw new FileRequestException('Could not connect using HTTP client!');
        }

        $path = '/'.trim($path, '/');
        $status = $this->client->get($path);

        // If async, return status, not response
        if ($this->async) {
            return $status;
        }

        if ($this->client->statusCode != 200) {
            throw new FileRequestException("Server answered with {$this->client->statusCode} status code!");
        }
        $result = $this->bodyAsArray();
        $this->client->close();
        return $result;
    }

    public function asyncGetResponse() {
        if (!$this->async) {
            return false;
        }
        $this->client->recv();
        $result = $this->bodyAsArray();
        $this->client->close();
        return $result;
    }

    /**
     * Check if content type is JSON and is so, decode it to array
     *
     * @return mixed
     */
    private function bodyAsArray() {
        if ($this->client->headers['content-type'] == 'application/json') {
            return json_decode($this->client->body, true);
        } else {
            return $this->client->body;
        }
    }

}