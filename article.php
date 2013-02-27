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

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !$nntp->command('ARTICLE ' . (string) $_GET['id'], 220)) {
    error($lang['article_error']);
}

$article                = new Template('article');
$article['group']       = $group;
$article['id']          = (string) $_GET['id'];
$article['message']     = '';
$article['signature']   = '';

$parser_state           = 1;

$mimecount              = $attachmentcount = 0;
$boundary               = $mimetype = $encoding = $charset = '';
$boundaries             = $headers = $masterheaders = $attachments = Array();

foreach ($nntp->getRawResults() as $line) {
    if ($line == ".\r\n") {
        break;
    } else if ($parser_state == 1 && ($line == "\n" || $line == "\r\n")) {
        $parser_state = 2;

        if (isset($headers['content-type'])) {
            if (preg_match('/charset=(["\']?)([\w-]+)\1/i', $headers['content-type'], $m)) {
                $charset = trim($m[2]);
            }

            if (preg_match('/boundary=(["\']?)(.+)\1/is', $headers['content-type'], $m)) {
                $boundary = $boundaries[] = trim($m[2]);
            }

            if (preg_match('/([^;]+)(;|\$)/', $headers['content-type'], $m)) {
                $mimetype = trim(strtolower($m[1]));

                ++$mimecount;
            }
        }

        if (isset($headers['content-transfer-encoding'])) {
            $encoding = strtolower(trim($headers['content-transfer-encoding']));
        }

        if ($mimetype && $mimetype != 'text/plain' && substr($mimetype, 0, 10) != 'multipart/') {
            if (isset($headers['content-type']) && preg_match('/name=(["\']?)(.+)\1/s', $headers['content-type'], $m)) {
                $name = trim($m[2]);
            } else if (isset($headers['content-disposition']) && preg_match('/filename=(["\']?)(.+)\1/s', $headers['content-disposition'], $m)) {
                $name = trim($m[2]);
            } else {
                $name = $lang->format('article_unnamed_attachment_x', ++$attachmentcount);
            }

            if (isset($headers['content-description'])) {
                $name .= ' ' . trim($headers['content-description']);
            }

            $attachments[] = Array(
                                   'name' => $name, 
                                   'id'   => $mimecount, 
                                   'type' => $mimetype
                                   );
        }

        $headers = Array();

        continue;
    } else if (substr($line, 0, 2) == '..') {
        $line = substr($line, 1);
    }

    if ($parser_state == 1) {
        $hline = explode(': ', $line, 2);

        if ($hline[0] && isset($hline[1])) {
            $key           = $last_key = strtolower($hline[0]);
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
                $mimetype = '';
                $headers  = Array();
            }

            continue;
        }

        if ($mimetype && $mimetype != 'text/plain') {
            continue;
        }

        switch ($encoding) {
            case 'quoted-printable':
                $line = quoted_printable_decode($line);
                break;
            case 'base64':
                $line = base64_decode($line);
                break;
        }

        if ($charset) {
            $line = to_utf8($line, $charset);
        }

        if (isset($buffer)) {
            $line = $buffer . $line;

            unset($buffer);
        } else if (substr($line, -2) != "\r\n") {
            $buffer = $line;

            continue;
        }

        $line = preg_replace('/((mailto|https?|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/', '<a href="\\1">\\1</a>\\3', htmlentities($line, ENT_NOQUOTES, 'UTF-8'));

        if ($parser_state != 3 && $line == "-- \r\n") {
            $old_parser_state = $parser_state;
            $parser_state     = 3;
        } else if ($parser_state == 3 && $line == "\r\n") {
            $parser_state = $old_parser_state;

            unset($old_parser_state);
        }

        if ($parser_state == 3) {
            $article['signature'] .= $line;
        } else {
            $article['message'] .= $line;
        }
    }
}

$attachment_list = '';

if ($attachments) {
    sort($attachments);

    foreach ($attachments as $block) {
        $item            = new Template('article_attachmentbit');
        $item['name']    = $block['name'];
        $item['id']      = $block['id'];
        $item['type']    = $block['type'];
        $item['group']   = $group;
        $item['article'] = $article['id'];

        $attachment_list .= $item;
    }
}

$references = Array();

if (!empty($masterheaders['references']) || !empty($masterheaders['in-reply-to'])) {
    $refs = explode(' ', !empty($masterheaders['references']) ? $masterheaders['references'] : $headers['in-reply-to']);


    foreach ($refs as $ref) {
        $ref = trim($ref);

        if (empty($ref) || !preg_match('/^<.+>$/', $ref) || !($res = $nntp->command('XPATH ' . $ref, 223, true)) || $res{0} == '/') {
            continue;
        }

        $references[] = explode('/', $res);
    }
}

$article['attachment_list'] = $attachment_list;
$article['headers']         = $masterheaders;
$article['attachments']     = !empty($attachment_list);
$article['references']      = $references;
$article['next']            = $nntp->command('ARTICLE ' . ($article['id'] + 1), 220);
$article['prev']            = ($article['id'] > 1);

echo new Template('header');
echo $article;
echo new Template('footer');

?>