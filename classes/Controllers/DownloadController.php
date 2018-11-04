<?php

namespace Controllers;

use Downloaders\YoutubeDownloader;

class DownloadController extends AbstractController {
    public function downloadVideo($request)
    {
//        $ytd = new YoutubeDownloader();
//        return $ytd->download($request['url']);

//        $availableFormats = shell_exec('youtube-dl -J https://www.youtube.com/watch?v=d-o3eB9sfls');
        $availableFormats = shell_exec('youtube-dl -J https://soundcloud.com/fijimacintosh/pink-prada-shoes-and-a-burberry-trench-coat-ft-noirillusions-prod-by-izak');

        return json_decode($availableFormats);
    }
}

