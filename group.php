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

if (($data = $nntp->command('GROUP ' . $group, 211)) === false) {
    error($lang['groups_error']);
}

$format = '';

if (isset($_GET['format']) && in_array(strtolower($_GET['format']), Array('rss', 'rdf'))) {
    $format = '_' . strtolower($_GET['format']);
}

$start = (isset($_GET['start']) ? (integer) $_GET['start'] : 0);

list(, $first, $last) = explode(' ', $data);

if (!$start || $start > ($last - 19) || $start < $first) {
    $start = ($last - $first) > 19 ? $last - 19 : $first;
}

$n = min($last, $start + 19);

if (!$nntp->command('XOVER ' . $start . '-' . $n, 224)) {
    error($lang['groups_error_xover']);
}

if (empty($format)) {
    $navigation             = new Template('groupview_navigation');
    $navigation['group']    = $group;
    $navigation['start']    = $start;
    $navigation['first']    = $first;
    $navigation['last']	    = $last;
}

$groups = '';

foreach($nntp->getResults(16384, "\t", 9) as $line) {
    $bit 		= new Template('groupview_bit' . $format);
    $bit['id']		= $line[0];
    $bit['subject']     = $line[1];
    $bit['author']      = $line[2];
    $bit['date']        = $line[3];
    $bit['lines']       = $line[7];
    $bit['group']	= $group;

    $last_date          = $bit['date'];

    $groups .= $bit;
}

$groupview             = new Template('groupview' . $format);
$groupview['group']    = $group;
$groupview['groups']   = $groups;
$groupview['date']     = $last_date;

if (empty($format)) {
    $groupview['navigation'] = $navigation;

    echo new Template('header');
    echo $groupview;
    echo new Template('footer');
} else {
    header('Content-type: text/xml');

    echo $groupview;
}

?>