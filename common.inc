<?php

function head($title="php news") {?>
<html>
<head>
 <title><?echo $title?></title>
 <link rel="stylesheet=" src="/style.css">
</head>
<body leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr bgcolor="#9999cc">
  <td>&nbsp;</td>
  <td align="right">
   <a href="http://www.php.net/"><img src="/i/l.gif" border="0" width="120" height="64" alt="php"></a>
  </td>
 </tr>
 <tr bgcolor="#333366">
  <td colspan="2"><img src="/i/s.gif" width="1" height="1" alt=""></td>
 </tr>
</table>
<?php
}

function foot() {?>
<hr noshade size="1" width="60%" align="right">
<div align="right">
<small>
written by jim winstead. no rights reserved.
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
    return "<a href=\"mailto:".htmlspecialchars(urlencode($ar[1]))."\">".htmlspecialchars($ar[2])."</a>";
  }
  if (preg_match("/^\s*\"?(.+?)\"?\s*<(.+)>\s*$/",$a,$ar)) {
    return "<a href=\"mailto:".htmlspecialchars(urlencode($ar[2]))."\">".htmlspecialchars($ar[1])."</a>";
  }
  if (ereg("@",$a)) {
    return "<a href=\"mailto:".htmlspecialchars(urlencode($a))."\">".htmlspecialchars($a)."</a>";
  }
  return htmlspecialchars($a);
}

function format_subject($s) {
  $s = preg_replace("/^(Re:\s*)?\[(PHP|PEAR)(-.*)\] /i", "\\1", $s);
  return htmlspecialchars($s);
}

function format_date($d) {
  $d = strtotime($d);
  return strftime("%c", $d);
}
