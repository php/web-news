<?php

require 'common.inc';
require 'nntp.inc';

$s = nntp_connect("news.php.net")
  or die("failed to connect to news server");

$res = nntp_cmd($s,"GROUP $group",211)
  or die("failed to get info on group");
list (,$f,$l,$g) = explode(" ", $res);
$i=(int)$i;
if (!$i || $i > $l - 20 || $i < $f) $i = $l - $f > 20 ? $l - 20 : $f;
$n = min($l+1,$i+20);
$res = nntp_cmd($s,"XOVER $i-$n", 224)
  or die("failed to get xover data\n");

switch($format) {
  case 'rss':
    header("Content-type: text/xml");
    echo '<?xml version="1.0" encoding="utf-8"?>';?>
<rss version="0.93">
<channel> 
 <title>news.php.net: <?echo $group?></title>
 <link>http://news.php.net/group.php?group=<?echo $group?></link>
 <description></description>
<?  break;
  case 'rdf':
    header("Content-type: text/xml");
    echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rdf:RDF
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns="http://my.netscape.com/rdf/simple/0.9/">
<channel>
  <title>news.php.net: <?echo $group?></title>
  <link>http://news.php.net/group.php?group=<?echo $group?></link>
  <description><?echo $group?> Mailinglist at news.php.net</description>
  <language>en-US</language>
</channel>
<?  
break;
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
# TODO: somehow determine the correct charset
$charset = "";

while ($line = fgets($s, 4096)) {
  if ($line == ".\r\n") break;
  $line = chop($line);
  list($n,$subj,$author,$odate,$messageid,$references,$bytes,$lines,$extra)
    = explode("\t", $line, 9);
/*  $date = date("H:i:s M/d/y", strtotime($odate)); */
  $date822 = date("r", strtotime($odate));

  switch($format) {
    case 'rss':
      echo "<item>\n";
      echo "<link>http://news.php.net/article.php?group=$group&amp;article=$n</link>\n";
      echo "<title>", format_subject($subj, $charset), "</title>\n";
      echo "<description>", htmlspecialchars(format_author($author)), "</description>\n";
      echo "<pubDate>$date822</pubDate>\n";
      echo "</item>\n";
      break;
    case 'rdf':
      echo "<item>\n";
      echo "<title>", format_subject($subj, $charset), "</title>\n";
      echo "<link>http://news.php.net/article.php?group=$group&amp;article=$n</link>\n";
      echo "<description>", htmlspecialchars(format_author($author, $charset)), "</description>\n";
      echo "<pubDate>$date822</pubDate>\n";
      echo "</item>\n";
      break;
    case 'html':
    default:
      echo "<tr>";
      echo "<td class=\"$class\"><a href=\"article.php?group=$group&amp;article=$n\">$n</a></td>";
      echo "<td class=\"$class\">";
      echo format_subject($subj, $charset);
      echo "</td>";
      echo "<td class=\"$class\">".format_author($author, $charset)."</td>\n";
      echo "<td align=\"center\" class=\"$class\"><tt>" . format_date($odate) . "</tt></td>\n";
      echo "<td align=\"right\" class=\"$class\">$lines</td>\n";
  }
  $class = $class == "even" ? "odd" : "even";
}

switch ($format) {
  case 'rss':
    echo "</channel>\n</rss>\n";
    break;
  case 'rdf':
    echo "</rdf:RDF>\n";
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
