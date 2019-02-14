<?php

namespace HttpServer;

use Config\ConfigManager;
use Router\Router;
use swoole_http_server;

class Server {

    public $swooleServer;
    public $router;

    private $host;
    private $port;
    private $https;
    private $sslCert;
    private $sslKey;
    private $http2;

    public function __construct(Router $router) {
        $this->router = $router;

        $config = ConfigManager::module('http');

        $this->host    = $config->get('host');
        $this->port    = (int)$config->get('port');
        $this->https   = (bool)$config->get('enable_https');
        $this->sslCert = $config->get('ssl_cert_file');
        $this->sslKey  = $config->get('ssl_key_file');
        $this->http2   = (bool)$config->get('enable_http2');

        $this->setRouterWorkflow();
    }

    public function initiateSwooleServer() {
        $this->swooleServer = new swoole_http_server($this->host, $this->port);

        if ($this->https) {
            $this->swooleServer->set([
                'ssl_cert_file' => $this->sslCert,
                'ssl_key_file' => $this->sslKey,
            ]);
        }
        if ($this->http2) {
            $this->swooleServer->set([
                'open_http2_protocol' => $this->http2
            ]);
        }

        $this->swooleServer->on("start", function ($server) {
            $protocol = $this->https ? 'https' : 'http';
            echo "HTTP server is started at $protocol://{$this->host}:{$this->port}".PHP_EOL;
        });

        return $this;
    }

    public function launch() {
        if (!$this->swooleServer) {
            $this->initiateSwooleServer();
        }
        $this->swooleServer->start();
    }

    private function setRouterWorkflow() {
        if (!$this->swooleServer) {
            $this->initiateSwooleServer();
        }

        // TODO: Request logger?
        $this->swooleServer->on("request", function ($request, $response) {
            $swooleRequest = new SwooleRequest($request);
            // TODO: Use application-level Response class instead plain return
            try {
                $responseString = $this->router->route($swooleRequest);
            } catch (\Exception $exception) {
                // TODO: Use error handling lib?
                $responseString = $exception->getMessage();
            }
            $response->end($responseString.PHP_EOL);
        });

        return $this;
    }

    public function __destruct() {
        $this->swooleServer = null;
    }

}