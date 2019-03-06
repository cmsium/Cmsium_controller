<?php

namespace Webgear;

/**
 * Class Application (Singleton)
 *
 * @package Webgear
 */
class Application {

    public $router;
    public $request;
    public $response;

    public static $instance;

    public static function getInstance($router = null) {
        if (static::$instance != null) {
            return static::$instance;
        }

        static::$instance = new static($router);
        return static::$instance;
    }

    public function __construct($router) {
        $this->router = $router;
    }

    public function handle($request, $response) {
        $this->request = $request;
        $this->response = $response;
    }

}