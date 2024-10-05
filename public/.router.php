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

// Shared
if (preg_match('#^/shared(.+?)(\\?.+)?$#', $requestUri, $matches)) {
    $file = 'shared' . $matches[1];
    if (file_exists($file)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file);
        if (preg_match('#\\.js$#', $file)) {
            $mime_type = 'application/javascript';
        }
        if (preg_match('#\\.css$#', $file)) {
            $mime_type = 'text/css';
        }
        header('Content-type: ' . $mime_type);
        readfile($file);
        finfo_close($finfo);
        error_log("GOT $file ($mime_type)");
        return true;
    }
}

return false;
