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

$s = nntp_connect(NNTP_HOST);
if (!$s) {
	error("Failed to connect to news server");
}

if ($group) {
	$res = nntp_cmd($s,"GROUP $group",211);
	if (!$res) {
		error("Failed to select group");
	}
}

$res = nntp_cmd($s, "ARTICLE $article",220);
if (!$res) {
	error("Failed to get article ". $article);
}

$emit = false;

$inheaders = 1; $headers = array();
$boundary = $charset = $encoding = "";
$mimecount = 0; // number of mime parts
$boundaries = array();
$lk = '';
while (!feof($s)) {
	$line = fgets($s, 4096);
	if ($line == ".\r\n") {
		break;
	}
	if ($inheaders && ($line == "\n" || $line == "\r\n")) {
		$inheaders = 0;
		if (!empty($headers['content-type'])
		&& preg_match("/charset=(\"|'|)(.+)\\1/is", $headers['content-type'], $m)) {
			$charset = trim($m[2]);
		}
		if (!empty($headers['content-type'])
		&& preg_match("/boundary=(\"|'|)(.+)\\1/is", $headers['content-type'], $m)) {
			$boundaries[] = trim($m[2]);
			$boundary = end($boundaries);
		}
		if (!empty($headers['content-type'])
		&& preg_match("/([^;]+)(;|\$)/", $headers['content-type'], $m)) {
			++$mimecount;
		}

		$emit = ($mimecount == $part);

		if (!empty($headers['content-transfer-encoding'])) {
			$encoding = strtolower(trim($headers['content-transfer-encoding']));
		}
		if ($emit) {
			/* check if content-type exist is made above */
			header('Content-Type: ' . $headers['content-type']);
			/* Do not rely on user-provided content-deposition header, generate own one to */
			/* make the content downloadable, do NOT use inline, we can't trust the attachment*/
			/* Downside of this approach: images should be downloaded before use */
			/* this is safer though, and prevents doing evil things on php.net domain */
			$contentdisposition = 'attachment';
			if (isset($headers['content-disposition'])
			&& preg_match('/filename=([\'"]?).+?\1/', $headers['content-disposition'], $m)) {
				$contentdisposition .= '; ' . $m[0];
			}
			header('Content-Disposition: ' . $contentdisposition);
			// if (isset($headers['content-disposition'])) {
			//	header('Content-Disposition: ' . $headers['content-disposition']);
			//}
			if (isset($headers['content-description'])) {
				header('Content-Description: ' . $headers['content-description']);
			}
		}

		continue;
	}
	# fix lines that started with a period and got escaped
	if (substr($line,0,2) == "..") {
		$line = substr($line,1);
	}
	if ($inheaders) {
		/* header fields can be split across lines: CRLF WSP where WSP */
		/* is a space (ASCII 32) or tab (ASCII 9) */
		if ($lk && ($line[0] == ' ' || $line[0] == "\t")) {
			$headers[$lk] .= $line;
		} else {
			@list($k,$v) = explode(": ", $line, 2);
			if ($k && $v) {
				$headers[strtolower($k)] = $v;
				$lk = strtolower($k);
			} // else not a header field
		}
	} else {

		if ($boundary
		&& substr($line,0,2) == '--'
		&& substr($line,2,strlen($boundary)) == $boundary) {

			$inheaders = 1;

			if (substr($line,2+strlen($boundary)) == '--') {
				# end of this container
				array_pop($boundaries);
				$boundary = end($boundaries);
			} else {
				/* next section starts with no headers */
				$headers = null;
			}

			continue;
		}

		if (!$emit) {
			continue;
		}

		switch($encoding) {
			case "quoted-printable":
			$line = quoted_printable_decode($line);
			break;
			case "base64":
			$line = base64_decode($line);
			break;
		}

		echo $line;

		/* done with attachment, no need to continue */
		if ($emit) {
			break;
		}
	}
}

