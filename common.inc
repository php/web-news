<?php # vim:ft=php:ts=2:sw=2:et

function head($title="PHP news") {
  header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <title><?echo $title?></title>
 <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr bgcolor="#9999cc">
  <td>
   <a href="index.php"><img src="i/l.gif" border="0" width="120" height="64" alt="php" /></a>
  </td>
  <td align="right" valign="bottom">
   PHP.net <a href="news://news.php.net/" clas="top">news server</a> web interface
  </td>
 </tr>
 <tr bgcolor="#333366">
  <td colspan="3"><img src="i/g.gif" width="1" height="1" alt="" /></td>
 </tr>
</table>
<?php
}

function foot() {?>
<hr noshade="noshade" size="1" width="60%" align="right" />
<div align="right">
<small>
written by <a href="http://trainedmonkey.com/">jim winstead</a>. no rights reserved. (<a href="http://cvs.php.net/cvs.php/php-news-web">source code</a>)
</small>
</div>
</body>
</html>
<?php
}

  function to_utf8($str, $charset)
  {
    $n = iconv(strlen($charset) ? $charset : "iso-8859-1", "utf-8", $str);
    if ($n === false)
      return $str;
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
  }
  else {
    $text = quoted_printable_decode($text);
  }
  return to_utf8($text, $charset);
}

function recode_header($header, $basecharset)
{
  if (strpos($header, "=?") === false)
    return to_utf8($header, $basecharset);
  return preg_replace("/=\\?(.+?)\\?([qb])\\?(.+?)\\?=/ie", "decode_header('\\1','\\2','\\3')", $header);
}

# this turns some common forms of email addresses into mailto: links
function format_author($a, $charset) {
  $a = recode_header($a, $charset);
  if (preg_match("/^\s*(.+)\s+\\(\"?(.+?)\"?\\)\s*$/",$a,$ar)) {
    return "<a href=\"mailto:".htmlspecialchars(urlencode($ar[1]))."\">".str_replace(" ", "&nbsp;", htmlspecialchars($ar[2]))."</a>";
  }
  if (preg_match("/^\s*\"?(.+?)\"?\s*<(.+)>\s*$/",$a,$ar)) {
    return "<a href=\"mailto:".htmlspecialchars(urlencode($ar[2]))."\">".str_replace(" ", "&nbsp;", htmlspecialchars($ar[1]))."</a>";
  }
  if (ereg("@",$a)) {
    return "<a href=\"mailto:".htmlspecialchars(urlencode($a))."\">".htmlspecialchars($a)."</a>";
  }
  return str_replace(" ", "&nbsp;", htmlspecialchars($a));
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
  return nl2br(htmlspecialchars($s));
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
  return $s;
}

function format_date($d) {
  $d = strtotime($d);
  $d = strftime("%c", $d);
  return str_replace(" ", "&nbsp;", $d);
}
