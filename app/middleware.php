<?php

// Pre-run callbacks
$pre->addPipe(function($request) {
    var_dump('test!!!');
});

// Post-run callbacks
$post->addPipe(function($response) {
    var_dump('post-test!!!');
});

// AuthGate routes callback
$auth->addPipe(function () {
    $auth = new \App\Auth\AuthGate;
    return $auth->check();
});