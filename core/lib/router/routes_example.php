<?php
namespace Router;

foreach (glob(__DIR__."/exceptions/*.php") as $value){
    require_once $value;
}
require_once "callbackHandler.php";
require_once "customCallbackHandler.php";
require_once "Router.php";
require_once "Routable.php";
require_once "Route.php";
require_once "Request.php";
require_once "example.php";


$request = new Request();

$router = new Router();
$router->defineCallbackHandler(new customCallbackHandler());

$router->get("/users/{user}", function ($user) use ($request) {
    var_dump($user);
});

$router->get("/pets", "Example", "hello")->before("hi")->after("qwe");


$router->route($request);