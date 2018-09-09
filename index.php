<?php

function curl_get($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.youtube.com/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec ($ch);
    curl_close ($ch);
    return $contents;
}

$videoInfo = curl_get('http://www.youtube.com/get_video_info?video_id=YFl2mCHdv24&el=embedded&ps=default&eurl=&hl=en_US');
//var_dump(explode('&', $videoInfo));

function get_url_map($data)
{
    preg_match('/stream_map=(.[^&]*?)&/i',$data,$match);

    if(!isset($match[1])) {
        return FALSE;
    }
    else {
        $fmt_url =  urldecode($match[1]);
        if(preg_match('/^(.*?)\\\\u0026/',$fmt_url,$match2)) {
            $fmt_url = $match2[1];
        }

        $urls = explode(',',$fmt_url);
        $tmp = array();
        foreach($urls as $url) {

            if(preg_match('/itag=([0-9]+)/i',$url,$um1))
            {
                if(preg_match('/url=(.*)&?/i',$url,$um2))
                {
                    $u = urldecode($um2[1]);
                    $tmp[$um1[1]] = $u;
                }
            }
        }

        $formats = array(
            '13' => array('3gp', '240p', '10'),
            '17' => array('3gp', '240p', '9'),
            '36' => array('3gp', '320p', '8'),
            '5'  => array('flv', '240p', '7'),
            '6'  => array('flv', '240p', '6'),
            '34' => array('flv', '320p', '5'),
            '35' => array('flv', '480p', '4'),
            '18' => array('mp4', '480p', '3'),
            '22' => array('mp4', '720p', '2'),
            '37' => array('mp4', '1080p', '1')
        );

        foreach ($formats as $format => $meta) {
            if (isset($tmp[$format])) {
                $videos[] = array('pref' => $meta[2], 'ext' => $meta[0], 'type' => $meta[1], 'url' => $tmp[$format]);
            }
        }
        return $videos;
    }
}

var_dump(get_url_map($videoInfo));