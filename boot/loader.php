<?php
// Bootstrapping the whole application

// Load core libraries
require_once dirname(__DIR__).'/boot/defaults.php';
require_once ROOTDIR.'/core/autoload.php';

// Load app routes
$router = new \Router\Router;
include ROOTDIR.'/app/routes.php';

// Build and load application instance
$application = \Webgear\Swoole\Application::getInstance($router);

// Register middleware callbacks
$plumber = \Plumber\Plumber::getInstance();
$pre = $plumber->buildPipeline('webgear.pre');
$post = $plumber->buildPipeline('webgear.post');
include ROOTDIR.'/app/middleware.php';

// Load app-specific configuration
$application->config = \Config\ConfigManager::module('app');

// Create MySQL connection
$application->db = new \DB\MysqlConnection();

// Load helper functions. Add file to helpers array to load it.
foreach (HELPERS as $helperFile) {
    include ROOTDIR.'/helpers/'.$helperFile;
}
