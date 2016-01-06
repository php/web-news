<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$requestUri = $_SERVER['REQUEST_URI'];
$matches = array();

// Paging through group messages
if (preg_match('#^/(php.+|svn.+|ug.+)/start/([0-9]+)#', $requestUri, $matches)) {
    $_GET['group'] = $matches[1];
    $_GET['i'] = $matches[2];
    include 'group.php';
    return true;
}

// Individual post
if (preg_match('#^/(php.+|svn.+|ug.+)/([0-9]+)#', $requestUri, $matches)) {
    $_GET['group'] = $matches[1];
    $_GET['article'] = $matches[2];
    include 'article.php';
    return true;
}

// Newsgroup main page
if (preg_match('#^/(php.+|svn.+|ug.+)(/)?$#', $requestUri, $matches)) {
    $_GET['group'] = $matches[1];
    include 'group.php';
    return true;
}

return false;
