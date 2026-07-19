<?php

$NNTP_HOST = 'localhost';
if (getenv('NNTP_HOST')) {
    $NNTP_HOST = getenv('NNTP_HOST');
}

$NEWS_WEB_BASE_URL = 'https://news.php.net';
if (getenv('NEWS_WEB_BASE_URL')) {
    $NEWS_WEB_BASE_URL = rtrim(getenv('NEWS_WEB_BASE_URL'), '/');
} elseif (PHP_SAPI == 'cli-server') {
    $NEWS_WEB_BASE_URL = 'http://' . $_SERVER['HTTP_HOST'];
}
