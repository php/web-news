<?php

define('NNTP_HOST', 'localhost');

function nntp_connect($server, $port = 119) {
	$s = @fsockopen($server, $port, $errno, $errstr, 30);
	if (!$s) {
		return false;
	}
	$hello = fgets($s, 1024);
	if (substr($hello,0,4) != "200 ") {
		return false;
	}
	return $s;
}

function nntp_cmd($conn, $command, $expected) {
	if (strlen($command) > 510) {
		die("command too long: $command");
	}
	fputs($conn, "$command\r\n");
	$res = fgets($conn, 1024);
	list($code, $extra) = explode(" ", $res, 2);
	return ($code == $expected) ? $extra : false;
}

function error($str) {
	head("PHP news : error");
	echo "<blockquote><strong>Error:</strong> $str</blockquote>\n";
	foot();
	die();
}

function head($title="PHP news") {
	header("Content-type: text/html; charset=utf-8");
	echo '<?xml version="1.0"?>' . "\n";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php echo $title?></title>
  <link rel="stylesheet" href="/style.css" type="text/css" />
 </head>
 <body>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="header">
   <tr>
    <td>
     <a href="/index.php"><img src="/i/l.gif" width="120" height="67" alt="PHP" /></a>
    </td>
    <td align="right" valign="bottom">
     PHP.net <a href="news://<?php echo $_SERVER['HTTP_HOST']; ?>/" class="top">news server</a> web interface
    </td>
   </tr>
  </table>
<?php
}

function foot() {?>
  <hr />
  <div class="small">
   Written by Jim Winstead. no rights reserved. (<a href="https://git.php.net/?p=web/news.git">source code</a>)
  </div>
 </body>
</html>
<?php
}

function to_utf8($str, $charset)
{
	$n = iconv($charset ? $charset : 'iso-8859-1', 'utf-8', $str);
	if ($n === false) {
		return $str;
	}
	return $n;
}

function decode_header($charset,$encoding,$text) {
	if (strtolower($encoding) == "b") {
		$text = base64_decode($text);
	} else {
		$text = quoted_printable_decode($text);
	}
	return to_utf8($text, $charset);
}

function recode_header($header, $basecharset) {
	if (strpos($header, "=?") === false) {
		return to_utf8($header, $basecharset);
	}
	return preg_replace("/=\\?(.+?)\\?([qb])\\?(.+?)(\\?=|$)/ie", "decode_header('\\1','\\2','\\3')", $header);
}

/* Email spam protection (taken from php-bugs-web) */
function spam_protect($txt) {
	$translate = array('@' => ' at ', '.' => ' dot ');

	/* php.net addresses are not protected! */
	if (preg_match('/^(.+)@php\.net/i', $txt)) {
		return $txt;
	} else {
		return strtr($txt, $translate);
	}
}


# this turns some common forms of email addresses into mailto: links
function format_author($a, $charset) {
	$a = recode_header($a, $charset);
	if (preg_match("/^\s*(.+)\s+\\(\"?(.+?)\"?\\)\s*$/",$a,$ar)) {
		return "<a href=\"mailto:".htmlspecialchars(urlencode(spam_protect($ar[1])), ENT_QUOTES, "UTF-8")."\" class=\"email fn n\">".str_replace(" ", "&nbsp;", htmlspecialchars($ar[2], ENT_QUOTES, "UTF-8"))."</a>";
	}
	if (preg_match("/^\s*\"?(.+?)\"?\s*<(.+)>\s*$/",$a,$ar)) {
		return "<a href=\"mailto:".htmlspecialchars(urlencode(spam_protect($ar[2])), ENT_QUOTES, "UTF-8")."\" class=\"email fn n\">".str_replace(" ", "&nbsp;", htmlspecialchars($ar[1], ENT_QUOTES, "UTF-8"))."</a>";
	}
	if (ereg("@",$a)) {
		$a = spam_protect($a);
		return "<a href=\"mailto:".htmlspecialchars(urlencode($a), ENT_QUOTES, "UTF-8")."\" class=\"email fn n\">".htmlspecialchars($a, ENT_QUOTES, "UTF-8")."</a>";
	}
	return str_replace(" ", "&nbsp;", htmlspecialchars($a, ENT_QUOTES, "UTF-8"));
}

function format_subject($s, $charset) {
	global $article;
	$s = recode_header($s, $charset);
	$s = preg_replace("/^(Re: *)?\[(PHP|PEAR)(-.*)?\] /i", "\\1", $s);
	// make this look better on the preview page..
	if (strlen($s) > 150 && !isset($article)) {
		$s = substr($s, 0, 150) . "...";
	} else {
		$s = wordwrap($s, 150);
	}
	return nl2br(htmlspecialchars($s, ENT_QUOTES, "UTF-8"));
}


function format_title($s, $charset) {
	global $article;
	$s = recode_header($s, $charset);
	$s = preg_replace("/^(Re: *)?\[(PHP|PEAR)(-.*)?\] /i", "\\1", $s);
	// make this look better on the preview page..
	if (strlen($s) > 150 && !isset($article)) {
		$s = substr($s, 0, 150) . "...";
	} else {
		$s = wordwrap($s, 150);
	}
	return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function format_date($d) {
	$d = strtotime($d);
	$d = strftime("%c", $d);
	return str_replace(" ", "&nbsp;", $d);
}
