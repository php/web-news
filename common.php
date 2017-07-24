<?php

require_once 'lib/Web/News/Nntp.php';
require_once 'lib/fMailbox.php';

$NNTP_HOST = 'localhost';
if (getenv('NNTP_HOST')) {
	$NNTP_HOST = getenv('NNTP_HOST');
}

define('NNTP_HOST', $NNTP_HOST);

function error($str) {
	head("PHP news : error");
	echo "<section class=\"content\"><blockquote><strong>Error:</strong> ".to_utf8($str)."</blockquote></section>\n";
	foot();
	die();
}

function head($title="PHP Mailing Lists (PHP News)") {
	header("Content-type: text/html; charset=utf-8");

?>
<!doctype html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="/fonts/Fira/fira.css" type="text/css" />
  <link rel="stylesheet" href="/style.css" type="text/css" />
  <link rel="shortcut icon" href="/favicon.ico">
 </head>
 <body>
  <header class="header">
   <nav class="header-inner">
    <a href="/" class="header-brand"><img src="//php.net/images/logos/php-logo.svg" class="header-brand-img" alt="PHP" height="24" width="48"><span class="header-brand-text">lists</span></a><ul class="header-menu">
      <li class="header-menu-item"><a class="header-menu-item-link" href="https://php.net/downloads.php">Downloads</a></li>
      <li class="header-menu-item"><a class="header-menu-item-link" href="https://php.net/docs.php">Documentation</a></li>
      <li class="header-menu-item"><a class="header-menu-item-link" href="https://php.net/get-involved.php">Get Involved</a></li>
      <li class="header-menu-item mod-active"><a class="header-menu-item-link" href="https://php.net/support.php">Help</a></li>
     </ul>
     <form class="search-form" action="https://php.net/search.php">
      <input class="search-input" value="" name="pattern" placeholder="Search">
     </form>
    <div class="menu-icon" onclick="document.querySelector('.menu-mobile').classList.toggle('hide')">☰ MENU</div>
     <ul class="menu-mobile hide">
      <li class="menu-mobile-item"><a class="menu-mobile-item-link" href="https://php.net/downloads.php">Downloads</a></li>
      <li class="menu-mobile-item"><a class="menu-mobile-item-link" href="https://php.net/docs.php">Documentation</a></li>
      <li class="menu-mobile-item"><a class="menu-mobile-item-link" href="https://php.net/get-involved.php">Get Involved</a></li>
      <li class="menu-mobile-item mod-active"><a class="menu-mobile-item-link" href="https://php.net/support.php">Help</a></li>
     </ul>
   </nav>
  </header>
<?php
}

function foot() {?>

 <footer class="footer">
    <ul class="footer-nav">
     <li class="footer-nav-item"><a class="footer-nav-item-link" href="https://php.net/copyright.php">Copyright © 2001-<?php echo date('Y'); ?> The PHP Group</a></li>
     <li class="footer-nav-item"><a class="footer-nav-item-link" href="https://php.net/my.php">My PHP.net</a></li>
     <li class="footer-nav-item"><a class="footer-nav-item-link" href="https://php.net/contact.php">Contact</a></li>
     <li class="footer-nav-item"><a class="footer-nav-item-link" href="https://php.net/sites.php">Other PHP.net sites</a></li>
     <li class="footer-nav-item"><a class="footer-nav-item-link" href="https://php.net/mirrors.php">Mirror sites</a></li>
     <li class="footer-nav-item"><a class="footer-nav-item-link" href="https://php.net/privacy.php">Privacy policy</a></li>
    </ul>
 </footer>
 </body>
</html>
<?php
}

function to_utf8($str, $charset = 'iso-8859-1')
{
	$n = iconv($charset , 'utf-8', $str);
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
	return preg_replace_callback(
		"/=\\?(.+?)\\?([qb])\\?(.+?)(\\?=|$)/i",
		function ($m) {
			return decode_header($m[1], $m[2], $m[3]);
		},
		$header
	);
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
function format_author($a, $charset = 'iso-8859-1') {
	$a = recode_header($a, $charset);
	if (preg_match("/^\s*(.+)\s+\\(\"?(.+?)\"?\\)\s*$/",$a,$ar)) {
		return "<a href=\"mailto:".htmlspecialchars(urlencode(spam_protect($ar[1])), ENT_QUOTES, "UTF-8")."\" class=\"email fn n\">".str_replace(" ", "&nbsp;", htmlspecialchars($ar[2], ENT_QUOTES, "UTF-8"))."</a>";
	}
	if (preg_match("/^\s*\"?(.+?)\"?\s*<(.+)>\s*$/",$a,$ar)) {
		return "<a href=\"mailto:".htmlspecialchars(urlencode(spam_protect($ar[2])), ENT_QUOTES, "UTF-8")."\" class=\"email fn n\">".str_replace(" ", "&nbsp;", htmlspecialchars($ar[1], ENT_QUOTES, "UTF-8"))."</a>";
	}
	if (strpos("@",$a) !== false) {
		$a = spam_protect($a);
		return "<a href=\"mailto:".htmlspecialchars(urlencode($a), ENT_QUOTES, "UTF-8")."\" class=\"email fn n\">".htmlspecialchars($a, ENT_QUOTES, "UTF-8")."</a>";
	}
	return str_replace(" ", "&nbsp;", htmlspecialchars($a, ENT_QUOTES, "UTF-8"));
}

function format_subject($s, $charset = 'iso-8859-1') {
	global $article;
	$s = recode_header($s, $charset);

	if ((($pos = strpos($s, '[PHP')) !== false || ($pos = strpos($s, '[PEAR')) !== false)) {
		if (($end_pos = strpos($s, ']', $pos)) !== false) {
			$s = ltrim(substr_replace($s, '', $pos, $end_pos - $pos + 1));
		}
	}

	// make this look better on the preview page..
	if (strlen($s) > 150 && !isset($article)) {
		$s = substr($s, 0, 150) . "...";
	} else {
		$s = wordwrap($s, 150);
	}
	return nl2br(htmlspecialchars($s, ENT_QUOTES, "UTF-8"));
}


function format_title($s, $charset = 'iso-8859-1') {
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
	$d = gmdate('r', $d);
	return str_replace(" ", "&nbsp;", $d);
}
