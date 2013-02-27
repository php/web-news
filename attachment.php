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
  |	Kalle Sommer Nielsen <kalle@php.net>                             |
  | Based on code by:                                                    |
  |     Jim Winstead <jimw@php.net>                                      |
  +----------------------------------------------------------------------+
*/

require 'include/common.inc';

if (!isset($_GET['group'])) {
    error($lang['groups_error']);
}

$group = preg_replace('@[^A-Za-z0-9.-]@', '', $_GET['group']);

if (!$nntp->command('GROUP ' . $group, 211)) {
    error($lang['groups_error']);
}

if (!isset($_GET['article']) || !is_numeric($_GET['article']) || !$nntp->command('ARTICLE ' . (string) $_GET['article'], 220)) {
    error($lang['article_error']);
}

if (!isset($_GET['id'])) {
    error($lang['attachment_error']);
}

$part                   = (integer) $_GET['id'];
$parser_state           = 1;

$emit                   = false;
$mimecount              = $attachmentcount = 0;
$boundary               = $encoding = $buffer = '';
$boundaries             = $headers = $masterheaders = $attachments = Array();

foreach ($nntp->getRawResults() as $line) {
    if ($line == ".\r\n") {
        break;
    } else if ($parser_state == 1 && ($line == "\n" || $line == "\r\n")) {
        $parser_state = 2;

        if (isset($headers['content-type'])) {
            if (preg_match('/boundary=(["\']?)(.+)\1/is', $headers['content-type'], $m)) {
                $boundary = $boundaries[] = trim($m[2]);
            }

            if (preg_match('/([^;]+)(;|\$)/', $headers['content-type'], $m)) {
                ++$mimecount;
            }

            $emit = ($mimecount == $part);
        }

        if (isset($masterheaders['content-transfer-encoding'])) {
            $encoding = strtolower(trim($masterheaders['content-transfer-encoding']));
        }

        $headers = Array();

        continue;
    } else if (substr($line, 0, 2) == '..') {
        $line = substr($line, 1);
    }

    if ($parser_state == 1) {
        $hline = explode(': ', $line, 2);

        if ($hline[0] && isset($hline[1])) {
            $key = $last_key = strtolower($hline[0]);

            if ($emit && isset($masterheaders[$key])) {
                continue;
            }

            $headers[$key] = $masterheaders[$key] = rtrim($hline[1]);
        } elseif (isset($last_key)) {
            $headers[$last_key]       .= $line;
            $masterheaders[$last_key] .= $line;

            unset($last_key);
        }
    } else {
        if ($boundary && substr($line, 0, 2) == '--' && in_array(substr($line, 2, strlen($boundary)), $boundaries)) {
            $parser_state = 1;

            if (substr($line, 2 + strlen($boundary)) == '--') {
                array_pop($boundaries);

                $boundary = end($boundaries);
            } else {
                $headers = Array();
            }

            $emit = false;

            continue;
        } else if (!$emit) {
            continue;
        }

        switch ($encoding) {
            case 'quoted-printable':
                $buffer .= quoted_printable_decode($line);
                break;
            case 'base64':
                $buffer .= base64_decode($line);
                break;
            default:
                $buffer .= $line;
        }
    }
}

if (empty($buffer)) {
    error($lang['attachment_error']);
}

$filename = $lang['attachment_filename'];

if (preg_match('/name=(["\']?)(.+)\1/s', $masterheaders['content-type'], $m)) {
    $filename = trim($m[2]);
}

header('Content-Type: ' . $headers['content-type']);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($buffer));

echo $buffer;

?>