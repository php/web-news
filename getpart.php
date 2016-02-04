<?php

require 'common.php';

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
	$nntpClient = new \Web\News\Nntp(NNTP_HOST);
	$message = $nntpClient->readArticle($article, $group);

	if ($message === null) {
		error('No article found');
	}

	$mail = fMailbox::parseMessage($message);
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
		$contentdisposition .= '; filename="' . $attachment['filename'] . '"';
	}

	header('Content-Type: ' . $attachment['mimetype']);
	header('Content-Disposition: ' . $contentdisposition);

	if (isset($attachment['description'])) {
		header('Content-Description: ' . $attachment['description']);
	}

	echo $attachment['data'];
} else {
	error('Part not found');
}
