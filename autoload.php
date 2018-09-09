<?php
require_once 'globals.php';

$classDirectories = [
    ''
];

function normalizeClass($class)
{
    if ($class[0] == '\\') {
        $class = substr($class, 1);
    }

    return str_replace(['\\', '_'], '/', $class) . '.php';
}

function __autoload($className)
{
    global $classDirectories;

    $className = normalizeClass($className);

    foreach ($classDirectories as $directory) {
        $path = BASE_DIR . 'classes/' . $directory . $className;
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
}