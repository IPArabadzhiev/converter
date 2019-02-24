<?php

namespace Controllers;

use Downloaders\YoutubedlDownloader;

class DownloadController extends AbstractController {
    function tailFile($filepath, $lines = 1) {
        return trim(implode("", array_slice(file($filepath), -$lines)));
    }
    function disable_ob() {
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Turn off PHP output compression
        ini_set('zlib.output_compression', false);
        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);
        // Clear, and turn off output buffering
        while (ob_get_level() > 0) {
            // Get the curent level
            $level = ob_get_level();
            // End the buffering
            ob_end_clean();
            // If the current level has not changed, abort
            if (ob_get_level() == $level) break;
        }
        // Disable apache output buffering/compression
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
            apache_setenv('dont-vary', '1');
        }
    }

    public function getVideoInformation($request)
    {
        return YoutubedlDownloader::getInstance()->getInformation($request['url']);
    }

    public function downloadVideo($request)
    {
        var_dump($request);
//        $ytd = new YoutubeDownloader();
//        return $ytd->download($request['url']);
        // error:(.*?)\.
        // .*?(\{.*\})
//        $availableFormats = shell_exec('youtube-dl -J https://www.youtube.com/watch?v=d-o3eB9sfls');
        $cmd = 'youtube-dl --newline --extract-audio --audio-format mp3 https://www.youtue.om/atch?v=d-o3eB9sfls 2>&1';
        $cmd = 'youtube-dl -J https://www.youtube.com/watch?v=d-o3eB9sfls 2>&1';
        $output = shell_exec($cmd);
        $matches = [];
//        var_dump($output);
        $error = null;
        $info = null;
        $success = false;

        // Check if there are any errors
        preg_match('/ERROR:(.*?)[\.:]/i', $output, $matches);
        if (! empty($matches)) {
            $error = $matches[1];
        } else {
            // There are sometimes warnings that we need to remove to check the validity of the output json about the video/audio
            // This regex will extract only the json part of the out if there is such part
            preg_match('/.*?(\{.*\})/i', $output, $matches);
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

        $this->disable_ob();
//        $availableFormats = system('youtube-dl --extract-audio --audio-format mp3 https://www.youtube.com/watch?v=d-o3eB9sfls');
        popen($cmd, 'r');
        while (@ ob_end_flush()); // end all output buffers if any

        $proc = popen($cmd, 'r');
        echo '<pre>';
        while (!feof($proc))
        {
            echo fread($proc, 4096) . '-' . PHP_EOL;
            @ flush();
        }
        echo '</pre>';

        pclose($proc);
        $r = shell_exec($cmd);
        return $r;
//        $availableFormats = shell_exec('youtube-dl -J https://soundcloud.com/fijimacintosh/pink-prada-shoes-and-a-burberry-trench-coat-ft-noirillusions-prod-by-izak');

//        return json_decode($availableFormats);
    }
}

