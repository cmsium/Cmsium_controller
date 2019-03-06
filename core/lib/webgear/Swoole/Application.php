<?php

namespace Webgear\Swoole;

use HttpServer\SwooleHttpApplication;
use Webgear\Application as GeneralApplication;
use Webgear\Exceptions\InvalidDataTypeException;

class Application extends GeneralApplication implements SwooleHttpApplication {

    public function handle($request, $response) {
        parent::handle($request, $response);

        $request = new \HttpServer\SwooleRequest($request);
        $result = $this->router->route($request);
        $this->respond($result);
    }

    public function setHeader($key, $value) {
        $this->response->header($key, $value);
        return $this;
    }

    public function setCookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
        return $this;
    }

    public function setStatusCode(int $code) {
        $this->response->status($code);
        return $this;
    }

    public function respond($result) {
        $response = $this->formResponseString($result);
        $this->response->end($response);
    }

    public function respondFile($filename, $offset = 0, $length = 0) {
        $this->response->sendFile($filename, $offset, $length);
    }

    private function formResponseString($response) {
        switch (gettype($response)) {
            case 'boolean':
            case 'array':
            case 'object':
                $result = json_encode($response);
                break;

            case 'string':
            case 'integer':
            case 'double':
                $result = (string)$response;
                break;

            default:
                throw new InvalidDataTypeException;
        }
        return $result;
    }

}