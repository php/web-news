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
  |    Kalle Sommer Nielsen <kalle@php.net>                             |
  | Based on code by:                                                    |
  |     Jim Winstead <jimw@php.net>                                      |
  +----------------------------------------------------------------------+
*/

class NNTP {
    protected $lang;
    protected $socket;

    public function __construct($server = 'localhost', $port = 119) {
        global $lang;

        $errno	= $errstr = '';
        $socket	= @fsockopen($server, $port, $errno, $errstr, 5);

        if (!$socket) {
            error($lang['nntp_error_connect']);
        } else if (substr(fgets($socket, 1024), 0, 4) != '200 ') {
            error($lang['nntp_error_handshake']);
        }

        $this->lang   = $lang;
        $this->socket = $socket;
    }

    public function command($command, $expected, $silent = false) {
        if (strlen($command) > 510) {
            if ($silent) {
                return false;
            }

            error($this->lang['nntp_error_command']);
        }

        fputs($this->socket, $command . "\r\n");
        list($code, $extra) = explode(' ', fgets($this->socket, 1024), 2);

        if ($code == $expected) {
            return $extra;
        }

        return false;
    }

    public function getResults($bytes = 1024, $char = ' ', $limit = 0) {
        $results = $this->getRawResults($bytes);

        foreach($results as $no => $line) {
            $results[$no] = ($limit ? explode($char, chop($line), $limit) : explode($char, chop($line)));
        }

        return $results;
    }

    public function getRawResults($bytes = 1024) {
        $results = Array();

        while ($line = fgets($this->socket, $bytes)) {
            if ($line == ".\r\n") {
                break;
            }

            $results[] = $line;
        }

        return $results;
    }
}

?>