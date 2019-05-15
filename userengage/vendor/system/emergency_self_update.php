<?php

/**
 * File from https://PrestaShow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @authors     PrestaShow.pl <kontakt@prestashow.pl>
 * @copyright   2019 PrestaShow.pl
 * @license     https://prestashow.pl/license
 */

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$filepath = dirname(__FILE__) . "/emergency_update.php";

/**
 * @param $url
 * @return bool|string
 */
function pshow_file_get_contents($url)
{
    $stream_context = @stream_context_create(array(
        "ssl" => array(
            "verify_peer"=> false,
            "verify_peer_name"=> false,
        ),
        "http" => array(
            "timeout" => 10,
        ),
    ));
    if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) ||
        !preg_match('/^https?:\/\//', $url)) {
        return @file_get_contents($url, false, $stream_context);
    } elseif (function_exists('curl_init')) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $opts = stream_context_get_options($stream_context);
        if (isset($opts['http']['method']) &&
            Tools::strtolower($opts['http']['method']) == 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            if (isset($opts['http']['content'])) {
                parse_str($opts['http']['content'], $post_data);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            }
        }
        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
    } else {
        return false;
    }
}

$file = pshow_file_get_contents("https://git.layersshow.com/Prestashow/changelog/raw/master/emergency_update.php");

if ($file && !empty($file)) {
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    file_put_contents($filepath, $file);
    die('OK');
}

die('FAILED');