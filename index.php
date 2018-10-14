<?php
// @TODO: stop errors for production
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'autoload.php';

use Router\Request;
use Router\Router;

$router = new Router(new Request());

$router->post('/download', 'DownloadController@downloadVideo');

$router->get('/', 'IndexController@index');