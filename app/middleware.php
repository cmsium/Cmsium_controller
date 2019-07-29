<?php

// Pre-run callbacks
$pre->addPipe(function($request) {
    // Implement
});

// Post-run callbacks
$post->addPipe(function($response) {
    // Implement
});

// AuthGate routes callback
$auth->addPipe(function () {
    $auth = new \App\Auth\AuthGate;
    return $auth->check();
});