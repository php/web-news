<?php

require 'common.inc';
require 'nntp.inc';

$s = nntp_connect("news.php.net")
  or die("failed to connect to news server");

$res = nntp_cmd($s,"GROUP $group",211)
  or die("failed to get info on group");
list (,$f,$l,$g) = explode(" ", $res);
if (!$i || $i > $l - 20 || $i < $f) $i = $l - $f > 20 ? $l - 20 : $f;
$n = min($l,$i+20);
$res = nntp_cmd($s,"XOVER $i-$n", 224)
  or die("failed to get xover data\n");
head($group);
echo '<table class="alist" width="100%">';
echo '<tr><td class="alisthead">#</td><td class="alisthead">subject</td><td class="alisthead">author</td><td class="alisthead">date</td><td class="alisthead">lines</td></tr>',"\n";
$class = "even";
while ($line = fgets($s, 4096)) {
  if ($line == ".\r\n") break;
  $line = chop($line);
  list($n,$subj,$author,$date,$messageid,$references,$bytes,$lines,$extra)
    = explode("\t", $line, 9);
  $date = date("H:i:s M/d/y", strtotime($date));
  echo "<tr>";
  echo "<td align=\"right\" class=\"$class\">$n</td>";
  echo "<td class=\"$class\">";
  echo "<a href=\"article.php?group=$group&amp;article=$n\">".format_subject($subj)."</a>";
  echo "</td>";
  echo "<td class=\"$class\">".format_author($author)."</td>\n";
  echo "<td class=\"$class\"><tt>$date</tt></td>\n";
  echo "<td align=\"right\" class=\"$class\">$lines</td>\n";
  $class = $class == "even" ? "odd" : "even";
}
echo "</table>\n";

foot();
