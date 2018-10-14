<?php

namespace Controllers;

use Downloaders\YoutubeDownloader;

class DownloadController extends AbstractController {
    public function downloadVideo()
    {
        $ytd = new YoutubeDownloader();
        $ytd->download('https://www.youtube.com/watch?v=Kyx2PsuwomE');

    }
}

