<?php

require 'common.php';

function sanitise_header_value($value)
{
    // Values must not contain control bytes; stripping them
    // prevents rejected or injected response headers.

    return trim(preg_replace('/[\x00-\x1F\x7F]/', '', (string) $value));
}

if (isset($_GET['group'])) {
    $group = preg_replace('@[^A-Za-z0-9.-]@', '', $_GET['group']);
} else {
    $group = false;
}

if (isset($_GET['article'])) {
    $article = (int)$_GET['article'];
} else {
    error("No article specified");
}

if (isset($_GET['part'])) {
    $part = $_GET['part'];
} else {
    error("No part specified");
}

try {
    $nntpClient = new \Web\News\Nntp($NNTP_HOST);
    $message = $nntpClient->readArticle($article, $group);

    if ($message === null) {
        error('No article found');
    }

    $mail = \Flourish\Mailbox::parseMessage($message);
} catch (Exception $e) {
    error($e->getMessage());
}

if (!empty($mail['attachment'][$part])) {
    $attachment = $mail['attachment'][$part];

    /* Do not rely on user-provided content-deposition header, generate own one to */
    /* make the content downloadable, do NOT use inline, we can't trust the attachment*/
    /* Downside of this approach: images should be downloaded before use */
    /* this is safer though, and prevents doing evil things on php.net domain */
    $contentdisposition = 'attachment';

    if (!empty($attachment['filename'])) {

        // Use a simple download name; attachment filenames
        // are not trusted message content.

        $filename = basename(str_replace('\\', '/', sanitise_header_value($attachment['filename'])));
    } else {
        $filename = '';
    }

    if ($filename === '') {
        $filename = 'attachment';
    }

    $contentdisposition .= '; filename="' . addcslashes($filename, '\\"') . '"';

    $mimetype = sanitise_header_value($attachment['mimetype']);

    // Only send a bare type/subtype MIME value; parameters
    // and malformed values fall back safely.

    if (!preg_match('#^[a-z0-9!#$&^_.+-]+/[a-z0-9!#$&^_.+-]+$#i', $mimetype)) {
        $mimetype = 'application/octet-stream';
    }

    header('X-Content-Type-Options: nosniff');
    header('Content-Security-Policy: sandbox');
    header('Content-Type: ' . $mimetype);
    header('Content-Disposition: ' . $contentdisposition);

    if (isset($attachment['description'])) {
        header('Content-Description: ' . sanitise_header_value($attachment['description']));
    }

    echo $attachment['data'];
} else {
    error('Part not found');
}
