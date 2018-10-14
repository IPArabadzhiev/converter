<?php

namespace Downloaders;

class YoutubeDownloader extends AbstractDownloader
{
    private $baseUrl = 'https://www.youtube.com/';
    private $videoInfoUrl = 'get_video_info?video_id=%s&el=embedded&ps=default&eurl=&hl=en_US';
    private $curlHandle;

    private $videoFormats = [
        '13' => ['3gp', '240p', '10'],
        '17' => ['3gp', '240p', '9'],
        '36' => ['3gp', '320p', '8'],
        '5'  => ['flv', '240p', '7'],
        '6'  => ['flv', '240p', '6'],
        '34' => ['flv', '320p', '5'],
        '35' => ['flv', '480p', '4'],
        '18' => ['mp4', '480p', '3'],
        '22' => ['mp4', '720p', '2'],
        '37' => ['mp4', '1080p', '1']
    ];

    public function __construct()
    {
        $this->curlHandle = new \Curl($this->baseUrl);
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    protected function getCurlHandle()
    {
        return $this->curlHandle;
    }

    protected function getDownloadsDir()
    {
        $dir = $this->getBaseDownloadsDir() . 'youtube/';
        if (! file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    public function download($url)
    {
        $videoId = $this->getVideoId($url);

        if ($videoId) {
            $videoInfoUrl = $this->baseUrl . sprintf($this->videoInfoUrl, $videoId);
            $videoInfo = $this->getCurlHandle()->get($videoInfoUrl);
            $videoUrlData = $this->getVideoUrlData($videoInfo);
            
            if ($videoUrlData) {
                usort($videoUrlData, function ($videoA, $videoB) {
                    return $videoA['pref'] - $videoB['pref'];
                });
                $videoToDownload = current($videoUrlData);

                $videoUrl = $videoToDownload['url'];
//                $res = $videoToDownload['type'];
                $ext = $videoToDownload['ext'];

                $videoTitle = $videoId;
                $videoFilename = "$videoTitle.$ext";
//                $thumbFilename = "$videoTitle.jpg";
                $video  = $this->getDownloadsDir() . $videoFilename;

                clearstatcache();

                /**
                 *  If the video does not already exist in the download directory,
                 *  try to download the video and the video preview image.
                 */
                if(true || ! file_exists($video))
                {
                    touch($video);
                    chmod($video, 0775);

                    var_dump($videoToDownload);

                    // Download the video.
                    var_dump($videoUrl);
                    var_dump($video);
                    $s = $this->getCurlHandle()->getFile($videoUrl, $video);
                    var_dump($s);
                    echo 'Download complete';
                    $audio =  $this->getDownloadsDir() . $videoTitle . '.mp3';
                    $cmd = "ffmpeg -i \"$video\" -ar 44100 -ab 320 -ac 2 \"$audio\"";
                    $f = exec($cmd);
                    var_dump($f);
                    echo 'Converting to mp3 complete';
                }
            }
        }
    }

    private function getVideoId($url)
    {
        $pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
        $pattern .= '(?:www\.)?';         #  Optional www subdomain.
        $pattern .= '(?:';                #  Group host alternatives:
        $pattern .=   'youtu\.be/';       #    Either youtu.be,
        $pattern .=   '|youtube\.com';    #    or youtube.com
        $pattern .=   '(?:';              #    Group path alternatives:
        $pattern .=     '/embed/';        #      Either /embed/,
        $pattern .=     '|/v/';           #      or /v/,
        $pattern .=     '|/watch\?v=';    #      or /watch?v=,
        $pattern .=     '|/watch\?.+&v='; #      or /watch?other_param&v=
        $pattern .=   ')';                #    End path alternatives.
        $pattern .= ')';                  #  End host alternatives.
        $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
        $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.
        preg_match($pattern, $url, $matches);
        return (isset($matches[1])) ? $matches[1] : false;
    }

    private function getVideoUrlData($videoInfo)
    {
        preg_match('/stream_map=(.[^&]*?)&/i', $videoInfo, $match);
        if(! isset($match[1])) {
            return false;
        } else {
            $fmtUrl = urldecode($match[1]);
            if (preg_match('/^(.*?)\\\\u0026/', $fmtUrl, $match2)) {
                $fmtUrl = $match2[1];
            }

            $urls = explode(',', $fmtUrl);
            $parsedVideosFromInfo = [];

            foreach ($urls as $url) {
                if (preg_match('/itag=([0-9]+)/si', $url, $tagMatches)) {
                    if (preg_match('/url=(.*)&?/si', $url, $urlMatches)) {
                        $u = urldecode($urlMatches[1]);
                        $parsedVideosFromInfo[$tagMatches[1]] = $u;
                    }
                }
            }

            $videos = [];
            foreach ($this->videoFormats as $format => $meta) {
                if (isset($parsedVideosFromInfo[$format])) {
                    $videos[] = [
                        'pref' => $meta[2],
                        'ext' => $meta[0],
                        'type' => $meta[1],
                        'url' => $parsedVideosFromInfo[$format]
                    ];
                }
            }
            return $videos;
        }
    }
}