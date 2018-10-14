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

use Downloaders\YoutubeDownloader;

$router = new Router(new Request());

$router->get('/download', function($request) {
    $ytd = new YoutubeDownloader();
    $ytd->download('https://www.youtube.com/watch?v=Kyx2PsuwomE');
});

$router->get('/', function($request) {
    echo '2';
});