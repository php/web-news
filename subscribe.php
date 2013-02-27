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
  +----------------------------------------------------------------------+
*/

require 'include/common.inc';
require 'include/ip-to-country.inc';
require 'include/posttohost.inc';

$group = preg_replace('@[^A-Za-z0-9.-]@', '', $_GET['group']);

if (($data = $nntp->command('GROUP ' . $group, 211)) === false) {
    error($lang['group_error']);
}

if (!isset($_POST['subscription_id']) || !filter_var($_POST['subscription_id'], FILTER_VALIDATE_EMAIL)) {
    error($lang['subscribe_error_email']);
}

list(, , $last) = explode(' ', $data);

if (!$nntp->command('ARTICLE ' . $last, 220)) {
    error($lang['subscribe_error_ml_data']);
}

$last_line = $newsgroup = '';

foreach ($nntp->getRawResults() as $line) {
    $last_line = $line = strtolower($line);

    if ($line == ".\r\n" || ($line == "\r\n" && $last_line == "\r\n")) {
        break;
    } elseif (substr($line, 0, 4) != 'to: ' && substr($line, 0, 4) != 'cc: ') {
        continue;
    }

    if (($newsgroup = parse_newsgroup_list(substr($line, 4))) !== false) {
        break;
    }
}

if (!$newsgroup) {
    error($lang['subscribe_error_ml_data']);
}

$newsgroup = substr($newsgroup, 0, strpos($newsgroup, '@'));
$request   = (isset($_POST['unsubscribe']) ? 'unsubscribe' : 'subscribe');
$result    = posttohost('http://master.php.net/entry/subscribe.php', Array(
                                                                           'request'  => $request, 
                                                                           'email'    => $_POST['subscription_id'], 
                                                                           'maillist' => $newsgroup, 
                                                                           'remoteip' => i2c_realip(), 
                                                                           'referer'  => 'http://news.php.net/subscribe/'
                                                                           ));

if ($result) {
    error($lang->format('subscribe_error_x_problem', $lang['subscribe_request_' . $request]));
}

redirect($lang['success'], $lang['subscribe_sucess_' . $request], SERVER_PATH . $group . '/');

?>