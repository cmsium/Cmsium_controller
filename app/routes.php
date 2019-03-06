<?php

$router->get('/hello', function() {
    $testVar = 'No way!';
    $pageOutput = view('index')->with(compact('testVar'))->render();
    return $pageOutput;
});
