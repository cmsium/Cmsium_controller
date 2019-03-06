<?php

$router = new Router();
$router->get("/users/{user}", function ($user){
    var_dump($user);
});

$router->post("/pets", "petsController", "create");