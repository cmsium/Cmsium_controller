<?php

function app() {
    return \Webgear\Swoole\Application::getInstance();
}

function view($template) : \Presenter\Page {
    return \Presenter\PageBuilder::getInstance()->build($template);
}

function plumber() : \Plumber\Plumber {
    return \Plumber\Plumber::getInstance();
}

function config($setting) : string {
    return \Webgear\Swoole\Application::getInstance()->config->get($setting);
}

function db() : \DB\MysqlConnection {
    return \Webgear\Swoole\Application::getInstance()->db;
}