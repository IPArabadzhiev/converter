<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'autoload.php';

//
//$ytd = new YoutubeDownloader();
//$ytd->download('https://www.youtube.com/watch?v=Kyx2PsuwomE');
////$ytd->download('https://www.youtube.com/watch?v=YFl2mCHdv24');
////$ytd->download('https://www.youtube.com/watch?v=BnYq7JapeDA');


use Router\Request;
use Router\Router;

$router = new Router(new Request());

$router->get('/download', 'DownloadController@downloadVideo');

$router->get('/', function($request) {
    echo '2';
});