<?php
// Bootstrapping the whole application

// Load core libraries
require_once dirname(__DIR__).'/boot/defaults.php';
require_once ROOTDIR.'/core/autoload.php';

// Load app routes
$router = new \Router\Router;
include ROOTDIR.'/app/routes.php';

// TODO: Load application classes
