<?php

namespace Webgear;

use Plumber\Plumber;

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

        // Register psr-4 autoloader for app classes
        $this->registerAppClassesLoader('app');
    }

    /**
     * Main handler called by web-server
     */
    public function handle($request, $response) {
        $this->request = $request;
        $this->response = $response;

        // Run pre-business middleware (request callbacks)
        $this->runMiddleware('pre');

        // Run app business logic, generating result
        $result = $this->run();

        // Run post-business middleware (response callbacks)
        $this->runMiddleware('post');

        // Finish request-response iteration
        $this->finish($result);
    }

    /**
     * Running business logic (presumably using router)
     *
     * To be implemented by children.
     */
    protected function run(){}

    /**
     * Finishes the request-response cycle by throwing away a response to web-server
     *
     * To be implemented by children.
     *
     * @param $result mixed Result of business logic
     */
    protected function finish($result){}

    protected function runMiddleware($context) {
        $argument = $context === 'pre' ? $this->request : $this->response;

        $plumber = Plumber::getInstance();
        $plumber->runPipeline("webgear.$context", $argument);
    }

    private function registerAppClassesLoader($directory) {
        $loader = new Autoloader;
        $loader->addNamespace('App', ROOTDIR."/$directory");
        $loader->register();
    }

}