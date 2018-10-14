<?php

namespace Controllers;

use Downloaders\YoutubeDownloader;

class DownloadController extends AbstractController {
    public function downloadVideo($request)
    {
        $ytd = new YoutubeDownloader();
        return $ytd->download($request['url']);
    }
}

