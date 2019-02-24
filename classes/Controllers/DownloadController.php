<?php

namespace Controllers;

use Downloaders\YoutubedlDownloader;

class DownloadController extends AbstractController
{
    public function getVideoInformation($request)
    {
        return YoutubedlDownloader::getInstance()->getInformation($request['url']);
    }

    public function convertVideo($request)
    {
        return YoutubedlDownloader::getInstance()->convertVideo($request['url']);
    }

    public function download($request)
    {
        return YoutubedlDownloader::getInstance()->download($request['fileName']);
    }
}

