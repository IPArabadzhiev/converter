<?php

class Curl
{
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36';
    private $referer;

    public function __construct($referer, $userAgent = null)
    {
        $this->referer = $referer;
        if (! empty($userAgent)) {
            $this->userAgent = $userAgent;
        }
    }

    public function get($url)
    {
        $ch = $this->initCurl($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec ($ch);
        curl_close ($ch);
        return $content;
    }

    public function getFile($remoteFileUrl, $localFileUrl)
    {
        $ch = $this->initCurl($remoteFileUrl);
        $fp = fopen($localFileUrl, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        $content = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        return $content;
    }

    private function initCurl($url)
    {
        $url = preg_replace('/\s/', '+', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        return $ch;
    }
}