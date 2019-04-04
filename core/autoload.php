<?php

function loadRecursive($path, $name) {
    $items = glob($path.DIRECTORY_SEPARATOR."*");

    foreach($items as $item) {
    $isPhp = (isset(pathinfo($item)["extension"]) && pathinfo($item)["extension"] === "php");

    if (is_file($item) && $isPhp && (basename($item) == "$name.php")) {
          include $item;
        } elseif (is_dir($item)) {
          loadRecursive($item, $name);
        }
    }
}

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/validation', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/router', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/config', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/http_server', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/plumber', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/utils/migrator', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/utils/openapi', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/database', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/database.p', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/files', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/files.p', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/config', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/validation', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/http_server', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/webgear', $className);
});

spl_autoload_register(function ($className) {
    $classNameArray = explode('\\', $className);
    $className = array_pop($classNameArray);
    loadRecursive('/Users/admin/Sites/Cmsium_file_services/Cmsium_controller/core/lib/presenter', $className);
});

