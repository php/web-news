<?php

define('NNTP_HOST', 'news.php.net');

//Rewrite strings for groups/articles
define('USE_REWRITE',true);
define('REWRITE_GROUP','%s');
define('REWRITE_GROUP_INDEX','%s/start/%d');
define('REWRITE_ARTICLE','%s/%d');

function error($str) {
	head("PHP news : error");
	echo "<blockquote><strong>Error:</strong> $str</blockquote>\n";
	foot();
	die();
}

function head($title="PHP news") {
	require("header.inc.php");
}

function foot() {
	require("footer.inc.php");
}

function to_utf8($str, $charset)
{
	$n = iconv($charset ? $charset : 'iso-8859-1', 'utf-8', $str);
	if ($n === false) {
		return $str;
	}
	return $n;
}

/*
if (function_exists("mb_convert_encoding")) {
function to_utf8($str, $charset)
{
return mb_convert_encoding($str, "utf-8", strlen($charset) ? $charset : "iso-8859-1");
}
} else if (function_exists("recode_string")) {
function to_utf8($str, $charset)
{
if (strlen($charset) == 0)
$charset = "iso-8859-1";
return recode_string("$charset..utf-8", $str);
}
} else if (function_exists("iconv")) {
function to_utf8($str, $charset)
{
return iconv(strlen($charset) ? $charset : "iso-8859-1", "utf-8", $str);
}
} else {
function to_utf8($str, $charset)
{
return $str;
}
}
*/

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

function get_group_link($group,$index=-1) {
	if($index===-1) {
		if(USE_REWRITE)
			return sprintf(REWRITE_GROUP,$group);
		else
			return sprintf("group.php?group=%s",urlencode($group));
	} else {
		if(USE_REWRITE)
			return sprintf(REWRITE_GROUP_INDEX,$group,$index);
		else
			return sprintf("group.php?group=%s&i=%d",urlencode($group),$index);
	}
}

function get_article_link($group,$article) {
	if(USE_REWRITE)
		return sprintf(REWRITE_ARTICLE,$group,$article);
	else
		return sprintf("article.php?group=%s&amp;article=%d",urlencode($group),$article);
}
