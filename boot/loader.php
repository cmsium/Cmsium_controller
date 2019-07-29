<?php
// Load external dependencies
require_once dirname(__DIR__).'/vendor/autoload.php';

// Bootstrapping the whole application
use App\Utils\SwooleServerCache;
use Config\ConfigManager;
use DB\MysqlConnection;
use Plumber\Plumber;
use Presenter\PageBuilder;
use Router\Router;
use Webgear\Swoole\Application;

// Load core libraries
require_once dirname(__DIR__).'/boot/defaults.php';
require_once dirname(__DIR__).'/core/autoload.php';

// Build and load application instance
$router = new Router;
$application = Application::getInstance($router);

// Load app routes
include ROOTDIR.'/app/routes.php';

// Register middleware callbacks
$plumber = Plumber::getInstance();
$pre = $plumber->buildPipeline('webgear.pre');
$post = $plumber->buildPipeline('webgear.post');
$auth = $plumber->buildPipeline('routes.auth');
include ROOTDIR.'/app/middleware.php';

// Load app-specific configuration
$application->config = ConfigManager::module('app');

// Create MySQL connection
$application->db = new MysqlConnection();

// Initiate a swoole table for server info caching
$application->serversCache = SwooleServerCache::getInstance();

// Add error handler to application
$application->errorHandler = new \Errors\AppErrorHandler(
    $application,
    PageBuilder::getInstance(),
    'error'
);

// Load helper functions. Add file to helpers array to load it.
foreach (HELPERS as $helperFile) {
    include ROOTDIR.'/helpers/'.$helperFile;
}

// TODO: Rework app environments handling
// Load AppTestCase if app env is test
if ($application->config->get('env') == 'test') {
    require_once ROOTDIR.'/tests/AppTestCase.php';
}