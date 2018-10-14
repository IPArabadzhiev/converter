<?php

namespace Downloaders;

abstract class AbstractDownloader
{
    abstract function __construct();

    abstract public function getBaseUrl();
    abstract public function download($url);

    abstract protected function getCurlHandle();
    abstract protected function getDownloadsDir();


    protected function getBaseDownloadsDir()
    {
        return BASE_DIR . 'downloads/';
    }
}