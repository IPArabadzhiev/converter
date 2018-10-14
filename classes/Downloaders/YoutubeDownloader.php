<?php

namespace Downloaders;

class YoutubeDownloader extends AbstractDownloader
{
    private $baseUrl = 'https://www.youtube.com/';
    private $videoInfoUrl = 'get_video_info?video_id=%s&el=embedded&ps=default&eurl=&hl=en_US&sts=%s';
    private $embedUrl = 'embed/%s';
    private $curlHandle;

    private $triesCount = 0;
    private $statusCode = 0;
    private $embedPage;
    private $playerJs;
    private $playerJsUrl;
    private $videoUrl;
    private $videoId;

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

        $this->setVideoUrl($url);

        $videoId = $this->getVideoId();
        $result = [
            'success' => false
        ];

        if ($videoId) {
//            $playerJsUrl = $this->getPlayerJsUrl($videoId);
//            print_r($playerJsUrl . PHP_EOL);
//            print_r($this->getPlayerJs());
//            return;
            $sts = $this->getSTS();
//            print_r($sts . PHP_EOL);
            $videoInfoUrl = $this->baseUrl . sprintf($this->videoInfoUrl, $videoId, $sts);
//            print_r($videoInfoUrl . PHP_EOL);
            $videoInfo = $this->getCurlHandle()->get($videoInfoUrl);
//            print_r($videoInfo . PHP_EOL);
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
                    $this->statusCode = $this->getCurlHandle()->getFile($videoUrl, $video);
                    if ($this->statusCode === 403) {
                        print_r($this->getPlayerJs());
                    }
//                    while ($this->statusCode !== 200 && $this->triesCount < 0) {
//                        $this->download($url);
//                        $this->triesCount++;
//                    }
                    $parts = parse_url($videoUrl);
                    parse_str($parts['query'], $query);
                    $result = [
                        'success' => $this->statusCode === 200,
                        'tries' => $this->triesCount,
                        'url' => $query
                    ];
                } else {
                    $result = [
                        'success' => true,
                        'message' => 'File existed'
                    ];
                }

                $audio =  $this->getDownloadsDir() . $videoTitle . '.mp3';
                $cmd = "ffmpeg -i \"$video\" -ar 44100 -ab 320 -ac 2 \"$audio\"";
                $f = exec($cmd);
            }
        }

        return $result;
    }

    private function getVideoId()
    {
        if (empty($this->videoId)) {
            $pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
            $pattern .= '(?:www\.)?';         #  Optional www subdomain.
            $pattern .= '(?:';                #  Group host alternatives:
            $pattern .= 'youtu\.be/';       #    Either youtu.be,
            $pattern .= '|youtube\.com';    #    or youtube.com
            $pattern .= '(?:';              #    Group path alternatives:
            $pattern .= '/embed/';        #      Either /embed/,
            $pattern .= '|/v/';           #      or /v/,
            $pattern .= '|/watch\?v=';    #      or /watch?v=,
            $pattern .= '|/watch\?.+&v='; #      or /watch?other_param&v=
            $pattern .= ')';                #    End path alternatives.
            $pattern .= ')';                  #  End host alternatives.
            $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
            $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.
            preg_match($pattern, $this->getVideoUrl(), $matches);
            $this->videoId = (isset($matches[1])) ? $matches[1] : false;
        }

        return $this->videoId;
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

    private function getEmbedPage()
    {
        if (empty($this->embedPage)) {
            $videoEmbedUrl = $this->baseUrl . sprintf($this->embedUrl, $this->getVideoId());
            $this->embedPage = $this->getCurlHandle()->get($videoEmbedUrl);
        }

        return $this->embedPage;
    }

    private function getPlayerJs()
    {
        if (empty($this->playerJs)) {
            $this->playerJs = $this->getCurlHandle()->get($this->getPlayerJsUrl());
        }

        return $this->playerJs;
    }

    private function getPlayerJsUrl()
    {
        if (empty($this->playerJsUrl)) {
            preg_match_all('/<script.*src=["\'](.*\/player.*.js)["\']/', $this->getEmbedPage(), $matches);
            if (isset($matches[1][0])) {
                $this->playerJsUrl = $this->baseUrl . $matches[1][0];
            } else {
                throw new \Exception('Cannot find video player');
            }
        }

        return $this->playerJsUrl;
    }

    private function getSTS()
    {
        preg_match_all('/["\']sts["\']:\s*(.*?)[,;}]/', $this->getEmbedPage(), $matches);
        if (isset($matches[1][0])) {
            return $matches[1][0];
        } else {
            throw new \Exception('Cannot find sts');
        }
    }

    private function getVideoUrl()
    {
        return $this->videoUrl;
    }

    private function setVideoUrl($videoUrl)
    {
        $this->videoUrl = $videoUrl;
    }
}