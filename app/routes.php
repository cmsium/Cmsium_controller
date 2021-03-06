<?php

$router->get("/file/{id}", "FileController", "getFile")->before('routes.auth');

$router->post("/file", "FileController", "uploadFile")->before('routes.auth');

$router->delete("/file/{id}", "FileController", "deleteFile")->before('routes.auth');

$router->delete("/service/file/{id}", "ServiceFileController", "deleteFile");
