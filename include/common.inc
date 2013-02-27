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

require 'include/nntp.inc';
require 'include/functions.inc';
require 'include/preferences.inc';
require 'include/language.inc';
require 'include/template.inc';

define('SERVER_NAME', 'www.tuxxedo.net');
define('SERVER_PATH', '/phpdotnet/news/');
define('NNTP_SERVER', 'news.php.net');
define('SCRIPT_NAME', end(explode('/', $_SERVER['SCRIPT_NAME'])));

$langs    = Array(
                  'en', 
                  'da'
                  );

$prefs    = new Preferences;
$lang     = new Language($prefs->language);
$nntp     = new NNTP(NNTP_SERVER);

?>