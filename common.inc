<?php /* vim: set ft=php : */

function head($title="php news") {
  header("Content-type: text/html; charset=iso-8859-1");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
 <title><?echo $title?></title>
 <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr bgcolor="#9999cc">
  <td>&nbsp;</td>
  <form method="GET" action="/search.php">
  <td valign="bottom">
   <b><a href="search.php">search</a> for:</b>
   <input type="text" name="q" value="<?echo htmlspecialchars($GLOBALS[q])?>" size="15">
   in 
   <select name="t">
    <option value="">all</option>
    <option>php.announce</option>
    <option>php.cvs</option>
    <option>php.db</option>
    <option>php.dev</option>
    <option>php.doc</option>
    <option>php.general</option>
    <option>php.gtk</option>
    <option>php.i18n</option>
    <option>php.install</option>
    <option>php.lang</option>
    <option>php.mirrors</option>
    <option>php.notes</option>
    <option>php.pear.dev</option>
    <option>php.pear.cvs</option>
    <option>php.pear.general</option>
    <option>php.qa</option>
    <option>php.template</option>
    <option>php.windows</option>
   </select>
   <input type="image" src="i/g.gif" border="0" width="11" height="11" value="go">
  </td>
  </form>
  <td align="right">
   <a href="index.php"><img src="i/l.gif" border="0" width="120" height="64" alt="php" /></a>
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

# this is pretty cheesy, since it doesn't do real charset handling
function decode_header($charset,$encoding,$text) {
  if (strtolower($encoding) == "b") {
    return base64_decode($text);
  }
  else {
    return quoted_printable_decode($text);
  }
}

# this turns some common forms of email addresses into mailto: links
function format_author($a) {
  $a = preg_replace("/=\\?(.+?)\\?([qb])\\?(.+?)\\?=/ie", "decode_header('\\1','\\2','\\3')", $a);
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

function format_subject($s) {
  global $article;
  $s = preg_replace("/=\\?(.+?)\\?([qb])\\?(.+?)\\?=/ie", "decode_header('\\1','\\2','\\3')", $s);
  $s = preg_replace("/^(Re: *)?\[(PHP|PEAR)(-.*)?\] /i", "\\1", $s);
  // make this look better on the preview page..
  if (strlen($s) > 150 && !isset($article)) {
	$s = substr($s, 0, 150) . "...";
  } else {
	$s = wordwrap($s, 150);
  }
  return nl2br(htmlspecialchars($s));
}

function format_date($d) {
  $d = strtotime($d);
  $d = strftime("%c", $d);
  return str_replace(" ", "&nbsp;", $d);
}
