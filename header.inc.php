<?php
header("Content-type: text/html; charset=utf-8");
echo "<?xml version=\"1.0\"?>\n"; /* allow it to work on servers with short_tags on */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?php echo $title?></title>
  <link rel="stylesheet" href="style.css" type="text/css" />
 </head>
 <body>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
   <tr class="header">
    <td>
     <a href="index.php"><img src="i/l.gif" width="120" height="67" alt="PHP" /></a>
    </td>
    <td align="right" valign="bottom">
     PHP.net <a href="news://<?php echo $_SERVER['HTTP_HOST']; ?>/" class="top">news server</a> web interface
    </td>
   </tr>
   <tr class="subheader">
    <td colspan="2">
     <img src="i/g.gif" width="1" height="1" alt="" />
    </td>
   </tr>
  </table>
