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

function message_wrapper($template, array $vars) {
    ob_get_clean();
    echo new Template('header');

    $wrapper = new Template($template);

    foreach ($vars as $var => $value) {
        $wrapper[$var] = $value;
    }

    echo $wrapper;
    echo new Template('footer');

    exit;
}

function error($message) {
    message_wrapper('error', Array(
                                   'error' => $message
                                   ));
}

function redirect($title, $message, $location) {
    message_wrapper('redirect', Array(
                                      'title'    => $title, 
                                      'message'  => $message, 
                                      'location' => $location
                                      ));
}

function to_utf8($text, $charset = 'iso-8859-1') {
    $charset = strtolower($charset);

    if ($charset == 'utf8' || $charset == 'utf-8') {
        return $text;
    }

    $converted = iconv($charset, 'utf-8', $text);

    if ($converted === false) {
        return $text;
    }

    return $converted;
}

function decode_header($charset, $encoding, $text) {
    if (strtolower($encoding) == 'b') {
        $text = base64_decode($text);
    } else {
        $text = quoted_printable_decode($text);
    }

    return to_utf8($text, $charset);
}

function recode_header($header, $basecharset) {
    if (strpos($header, '=?') === false) {
        return to_utf8($header, $basecharset);
    }

    return preg_replace('/=\\?(.+?)\\?([qb])\\?(.+?)(\\?=|$)/ie', 'decode_header("\\1", "\\2", "\\3")', $header);
}

function spam_protect($email) {
    if (preg_match('/^(.+)@php\.net/i', $email)) {
        return $email;
    }
	
    return strtr($email, Array('@' => ' at ', '.' => ' dot '));
}

function parse_newsgroup_list($authors) {
    foreach (explode(',', $authors) as $author) {
        $author = $email = recode_header(trim($author), 'ISO-8859-1');

        if (preg_match('/^\s*(.+)\s+\\("?(.+?)"?\\)\s*$/', $author, $ar)) {
            $email = $ar[1];
        } else if (preg_match('/^\s*"?(.+?)"?\s*<(.+)>\s*$/', $author, $ar)) {
            $email = $ar[2];
        }

        if (($pos = strpos($email, '@')) !== false && substr($email, $pos) == '@lists.php.net') {
            return $email;
        }
    }

    return false;
}

function format_author($author) {
    $author = recode_header($author, 'ISO-8859-1');

    if (preg_match('/^\s*(.+)\s+\\("?(.+?)"?\\)\s*$/', $author, $ar)) {
        return '<a href="mailto:' . htmlspecialchars(urlencode(spam_protect($ar[1]))) . '">' . str_replace('_', ' ', htmlspecialchars($ar[2])) . '</a>';
    } else if (preg_match('/^\s*"?(.+?)"?\s*<(.+)>\s*$/', $author, $ar)) {
        return '<a href="mailto:' . htmlspecialchars(urlencode(spam_protect($ar[2]))) . '">' . str_replace('_', ' ', htmlspecialchars($ar[1])) . '</a>';
    } else if (strpos($author, '@') !== false) {
        $author = spam_protect($author);

        return '<a href="mailto:' . htmlspecialchars(urlencode($author)) . '">' . htmlspecialchars($author) . '</a>';
    }

    return htmlspecialchars($author);
}

function format_date($date) {
    return strftime('%c', strtotime($date));
}

function format_xml_date($date) {
    return date('r', strtotime($date));
}

function format_subject($subject, &$is_svn = false) {
    $is_svn  = false;
    $subject = recode_header($subject, 'iso-8859-1');

    if ((($pos = strpos($subject, '[PHP')) !== false || ($pos = strpos($subject, '[PEAR')) !== false)) {
        if (($end_pos = strpos($subject, ']', $pos)) !== false) {
            $subject = ltrim(substr_replace($subject, '', $pos, $end_pos - $pos + 1));
        }
    }

    if (strlen($subject) > 60) {
        $subject = substr($subject, 0, 60) . '...';
    } else {
        $subject = wordwrap($subject, 60);
    }

    if (substr($subject, 0, 4) == 'svn:') {
        $is_svn  = true;
        $subject = str_replace('_', ' ', $subject);
    }

    return nl2br(htmlspecialchars($subject));
}

function format_newsgroups($newsgroups) {
    $groups = '';
    $split  = explode(',', $newsgroups);
    $size   = sizeof($split) - 1;

    sort($split);

    foreach ($split as $index => $group) {
        $groups .= '<a href="' . SERVER_PATH . $group . '/">' . $group . '</a>' . ($index != $size ? '<br />' : '');
    }

    return $groups;
}

function format_references(array $references) {
    global $lang;

    $refs = '';
    $ref  = 0;
    $size = sizeof($references) - 1;

    foreach ($references as $index => $block) {
        $refs .= '<a href="' . SERVER_PATH . $block[0] . '/' . $block[1] . '/">' . $lang->format('reference_x', ++$ref) . '</a>' . ($index != $size ? '<br />' : '');
    }

    return $refs;
}

function format_message($message, $depth = 1) {
    $message = ($depth == 1 ? nl2br($message) : $message);

    $quoted  = $buffer = '';
    $parsing = false;

    foreach (explode('<br />', $message) as $line) {
        if (empty($line)) {
            continue;
        } else if (substr(ltrim($line), 0, 4) == '&gt;') {
            if (!$parsing) {
                $parsing = true;
                $quoted  .= '<div class="quote-depth depth-' . $depth++ . '">' . "\r\n";
            }

            $buffer .= substr(ltrim($line), 4) . '<br />';
        } else {
            if ($parsing) {
                $quoted .= format_message($buffer, $depth) . '</div>' . "\r\n";
                $buffer = '';

                --$depth;
            }

            $parsing = false;
            $quoted  .= $line . '<br />';
        }
    }

    if ($parsing) {
        $quoted .= format_message($buffer, $depth) . '</div>' . "\r\n";
    }

    return $quoted;
}

?>