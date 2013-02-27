<?php
/*
  +----------------------------------------------------------------------+
  | PHP.net Website Systems                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2011 The PHP Group                                     |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  |	Jim Winstead <jimw@php.net>                                      |
  +----------------------------------------------------------------------+
*/

function posttohost($url, $data) {
    global $lang;

    $url = parse_url($url);

    if (!$url) {
        return $lang['posttohost_error_parse'];
    }

    if (!isset($url['port'])) {
        $url['port'] = '';
    }

    if (!isset($url['query'])) {
        $url['query'] = '';
    }

    $encoded = '';
    foreach ($data as $key => $value) {
        $encoded .= ($encoded ? '&' : '');
        $encoded .= rawurlencode($key) . '=' . rawurlencode($value);
    }

    $fp = @fsockopen($url['host'], $url['port'] ? $url['port'] : 80);

    if (!$fp) {
        return $lang->format('posttohost_error_connect_x', $url['host']);
    }

    fputs($fp, sprintf('POST %s%s%s HTTP/1.0' . "\n", $url['path'], $url['query'] ? '?' : '', $url['query']));
    fputs($fp, 'Host: ' . $url['host'] . "\n");
    fputs($fp, 'Content-type: application/x-www-form-urlencoded' . "\n");
    fputs($fp, 'Content-length: ' . strlen($encoded) . "\n");
    fputs($fp, 'Connection: close' . "\n\n");
    fputs($fp, $encoded . "\n");

    if (!preg_match('#^HTTP/1\\.. 200#', fgets($fp, 1024))) {
        return;
    }

    $results  = '';
    $inheader = true;

    while(!feof($fp)) {
        $line = fgets($fp, 1024);

        if ($inheader && ($line == "\n" || $line == "\r\n")) {
            $inheader = false;
        } else if (!$inheader) {
            $results .= $line;
        }
    }

    fclose($fp);

    return $results;
}

?>