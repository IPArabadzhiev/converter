<?php
/**
 * Created by PhpStorm.
 * User: PC-Admin
 * Date: 2/24/2019
 * Time: 12:33 PM
 */

namespace Downloaders;

/**
 * Class YoutubedlDownloader
 * @package Downloaders
 *
 * Downloads video and/or audio using the youtube-dl package
 */
class YoutubedlDownloader
{
    private static $instance = null;

    const REGEX_CHECK_ERROR = '/ERROR:(.*?)[\.:]/i';
    const REGEX_EXTRACT_JSON = '/.*?(\{.*\})/i';

    private function _construct() { }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new YoutubedlDownloader();
        }

        return self::$instance;
    }

    public function getInformation($url)
    {
        $cmd = "youtube-dl -J $url 2>&1";
        $output = shell_exec($cmd);
        $matches = [];
        $error = null;
        $info = null;
        $success = false;

        // Check if there are any errors
        preg_match(self::REGEX_CHECK_ERROR, $output, $matches);
        if (! empty($matches)) {
            $error = $matches[1];
        } else {
            // There are sometimes warnings that we need to remove to check the validity of the output json about the video/audio
            // This regex will extract only the json part of the out if there is such part
            preg_match(self::REGEX_EXTRACT_JSON, $output, $matches);
            if (! empty($matches)) {
                $data = json_decode($matches[1], true);
                // if there are no errors parsing the json and it is not empty we can conclude that the operation was a success
                if (
                    json_last_error() === JSON_ERROR_NONE
                    && !empty($data)
                ) {
                    $success = true;
                    $info = $data;
                }
            }
        }

        return [
            'error' => $error,
            'success' => $success,
            'info' => $info
        ];
    }
}