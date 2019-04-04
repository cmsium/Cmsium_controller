<?php
$router->get("/file/{id}", "FileController", "getFile");
$router->delete("/file/{id}", "FileController", "deleteFile");
$router->post("/file", "FileController", "uploadFile");
