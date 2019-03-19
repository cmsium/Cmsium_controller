<?php

function app() {
    return \Webgear\Swoole\Application::getInstance();
}

function view($template) {
    return \Presenter\PageBuilder::getInstance()->build($template);
}

function plumber() {
    return \Plumber\Plumber::getInstance();
}