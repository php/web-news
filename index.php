<?php

require 'common.inc';
require 'nntp.inc';

$s = nntp_connect(NNTP_HOST)
  or die("failed to connect to news server");

nntp_cmd($s,"LIST",215)
  or die("failed to get list of news groups");
head();

echo '<table border="0" cellpadding="6" cellspacing="0"><tr><td>';

echo "<table class=\"grouplist\">\n";
echo '<tr><td class="grouplisthead">name</td><td class="grouplisthead">messages</td><td class="grouplisthead">rss</td><td class="grouplisthead">rdf</td></tr>',"\n";
$class = "even";
while ($line = fgets($s, 1024)) {
  if ($line == ".\r\n") break;
  $line = chop($line);
  list($group,$high,$low,$active) = explode(" ", $line);
  echo "<tr>";
  echo "<td class=\"$class\">";
  echo "<a class=\"active$active\" href=\"/$group\">$group</a>";
  echo "</td>\n";
  echo "<td align=\"right\" class=\"$class\">", $high-$low+1, "</td>\n";
  echo "<td class=\"$class\">";
  if ($active != 'n') {
    echo "<a href=\"group.php?group=$group&amp;format=rss\">rss</a>";
  }
  echo "</td>\n";
  echo "<td class=\"$class\">";
  if ($active != 'n') {
    echo "<a href=\"group.php?group=$group&amp;format=rdf\">rdf</a>";
  }
  echo "</td>\n";
  echo "</tr>\n";
  $class = $class == "even" ? "odd" : "even";
}
echo "</table>\n";

echo '</td><td valign="top">';
?>
<h1>Welcome!</h1>
<p>This is a completely experimental interface to the PHP mailing
lists as reflected on the <a href="news://<?php echo $_SERVER['HTTP_HOST']; ?>/">
<?php echo $_SERVER['HTTP_HOST']; ?> NNTP server</a>.</p>
<p>There may be a little more info <a href="README">here</a>.</p>
<p>The news server software that is used is also available from <a
href="http://trainedmonkey.com/colobus/">here</a>.</p>
<?
echo '</td></tr></table>';

foot();
