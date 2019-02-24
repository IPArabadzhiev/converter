<?php
// @TODO: stop errors for production
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'autoload.php';

use Router\Request;
use Router\Router;

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header('Access-Control-Allow-Headers: X-Requested-With');

$router = new Router(new Request());

$router->post('/convertVideo', 'DownloadController@convertVideo');
$router->post('/download', 'DownloadController@downloadVideo');
$router->post('/getInformation', 'DownloadController@getVideoInformation');

$router->get('/', 'IndexController@index');
$router->get('/download', 'DownloadController@download');