<?php

require 'common.inc';
require 'nntp.inc';

$s = nntp_connect("news.php.net")
  or die("failed to connect to news server");

$res = nntp_cmd($s,"GROUP $group",211)
  or die("failed to get info on group");
list (,$f,$l,$g) = explode(" ", $res);
if (!$i || $i > $l - 20 || $i < $f) $i = $l - $f > 20 ? $l - 20 : $f;
$n = min($l+1,$i+20);
$res = nntp_cmd($s,"XOVER $i-$n", 224)
  or die("failed to get xover data\n");

switch($format) {
  case 'rss':
    header("Content-type: text/xml");
    echo "<?xml version=\"1.0\" standalone=\"yes\">\n";?>
<rss version="0.93">
<channel> 
 <title>news.php.net: <?echo $group?></title>
 <link>http://news.php.net/group.php?group=<?echo $group?></link>
 <description></description>
<?  break;
  case 'html':
  default:
    head($group);
    navbar($group,$f,$l,$i);
    echo '<table class="alist" width="100%">';
    echo '<tr><td class="alisthead">#</td><td class="alisthead">subject</td><td class="alisthead">author</td><td class="alisthead">date</td><td class="alisthead">lines</td></tr>',"\n";
    break;
}

# list of articles
$class = "even";
while ($line = fgets($s, 4096)) {
  if ($line == ".\r\n") break;
  $line = chop($line);
  list($n,$subj,$author,$odate,$messageid,$references,$bytes,$lines,$extra)
    = explode("\t", $line, 9);
  $date = date("H:i:s M/d/y", strtotime($odate));
  $date822 = date("r", strtotime($odate));

  switch($format) {
    case 'rss':
      echo "<item>\n";
      echo "<link>http://news.php.net/article.php?group=$group&amp;article=$n</link>\n";
      echo "<title>", format_subject($subj), "</title>\n";
      echo "<description>", htmlspecialchars(format_author($author)), "</description>\n";
      echo "<pubDate>$date822</pubDate>";
      echo "</item>\n";
      break;
    case 'html':
    default:
      echo "<tr>";
      echo "<td class=\"$class\"><a href=\"article.php?group=$group&amp;article=$n\">$n</a></td>";
      echo "<td class=\"$class\">";
      echo format_subject($subj);
      echo "</td>";
      echo "<td class=\"$class\">".format_author($author)."</td>\n";
      echo "<td class=\"$class\"><tt>$date</tt></td>\n";
      echo "<td align=\"right\" class=\"$class\">$lines</td>\n";
  }
  $class = $class == "even" ? "odd" : "even";
}

switch ($format) {
  case 'rss':
    echo "</channel></rss>\n";
    break;
  case 'html':
  default:
    echo "</table>\n";
    navbar($group,$f,$l,$i);
    foot();
}

function navbar($g,$f,$l,$i) {
  echo '<table border="0" cellpadding="2" cellspacing="2" width="100%"><tr class="alisthead">';
  echo '<td width="20%">';
  if ($i > $f) {
    $p = max($i-20,$f);
    echo "<a href=\"group.php?group=".htmlspecialchars($g)."&amp;i=$p\"><b>&laquo; previous</b></a>";
  }
  else {
    echo "&nbsp;";
  }
  echo '</td>';
  $j = min($i+20,$l);
  $c = $l - $f + 1;
  echo '<td align="center" class="alisthead" width="60%">'.htmlspecialchars($g)." ($i-$j of $c)</td>";
  echo '<td align="right" width="20%">';
  if ($i+20 < $l) {
    $n = min($i+20,$l-20);
    echo "<a href=\"group.php?group=".htmlspecialchars($g)."&amp;i=$n\"><b>next &raquo;</b></a>";
  }
  else {
    echo "&nbsp;";
  }
  echo '</td>';
  echo '</tr></table>';
}
