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

    const CONVERT_DEFAULT_FORMAT = 'mp3';

    const DOWNLOADS_PATH = 'downloads';

    private function _construct() { }

    private function disable_ob() {
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

    public function convertVideo($url, $format = self::CONVERT_DEFAULT_FORMAT)
    {
        $response = [
            'success' => false,
            'fileName' => '',
            'format' => $format
        ];
        $fileName = shell_exec("youtube-dl --get-filename $url");
        // remove extension
        $fileName = explode('.', $fileName);
        $fileName = $fileName[0];
        $response['fileName'] = $fileName;
        $downloadsPath = self::DOWNLOADS_PATH;
        if (file_exists("$downloadsPath/$fileName.$format")) {
            $response['success'] = true;
        } else {
            $cmd = "cd $downloadsPath && youtube-dl --newline --extract-audio --audio-format $format $url";
            $this->disable_ob();
            popen($cmd, 'r');
            while (@ ob_end_flush()) ; // end all output buffers if any

            $proc = popen($cmd, 'r');
            while (!feof($proc)) {
                $result = fread($proc, 4096);
                preg_match('/\[download]\s*(.*)%/i', $result, $matches);
                $progress = isset($matches[1]) ? floor($matches[1]) : 0;
                echo $progress . '|';
                @flush();
            }
            pclose($proc);
        }

        return $response;
    }

    public function download($fileName, $format = self::CONVERT_DEFAULT_FORMAT)
    {
        $downloadsPath = self::DOWNLOADS_PATH;
        $file = "$downloadsPath/$fileName.$format";

        if (file_exists($file)) {
//            switch(strtolower(substr(strrchr($file, '.'), 1))) {
//                case 'pdf':
//                    $mime = 'application/pdf';
//                    break;
//                case 'zip':
//                    $mime = 'application/zip';
//                    break;
//                case 'mp3':
//                    $mime = 'audio/mpeg';
//                    break;
//                case 'jpeg':
//                case 'jpg':
//                    $mime = 'image/jpg';
//                    break;
//                default:
//                    $mime = 'application/force-download';
//            }
//
//            header('Pragma: public'); 	// required
//            header('Expires: 0');		// no cache
//            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//            header('Cache-Control: private',false);
//            header('Content-Type: application/octet-stream');
//            header('Content-Disposition: attachment; filename="' . basename($file). '"');
//            header('Content-Transfer-Encoding: binary');
//            header('Content-Length: ' . filesize($file));	// provide file size
//            header('Connection: close');
//            readfile($file);
//            exit();
////
            header('Content-Type: audio/mpeg');
            header('Content-Disposition: attachment; filename=' . $fileName . '.' . $format);
            header('Content-length: ' . filesize($file));
            header('Cache-Control: no-cache');
            header('Content-Transfer-Encoding: binary');
            readfile($file);
            die();
//
//            header('Access-Control-Allow-Origin: *');
//            header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . $file);
//            die();
        }
    }
}