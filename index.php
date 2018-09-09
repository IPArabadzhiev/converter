<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'autoload.php';

use Downloaders\YoutubeDownloader;

$ytd = new YoutubeDownloader();
//$ytd->download('https://www.youtube.com/watch?v=o8NPllzkFhE');
//$ytd->download('https://www.youtube.com/watch?v=YFl2mCHdv24');
$ytd->download('https://www.youtube.com/watch?v=BnYq7JapeDA');


