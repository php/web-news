<?php

if (!$article) die("no article specified");

require 'common.inc';
require 'nntp.inc';

$s = nntp_connect("news.php.net")
  or die("failed to connect to news server");

if ($group) {
  $res = nntp_cmd($s,"GROUP $group",211)
    or die("failed to select group $group");
}
$res = nntp_cmd($s, "ARTICLE $article",220)
  or die("failed to get article $article");

$inheaders = 1; $headers = array();
while (!feof($s)) {
  $line = fgets($s, 4096);
  if ($line == ".\r\n") break;
  if ($inheaders && $line == "\r\n") {
    $inheaders = 0;
    head("$group: ".format_subject($headers[subject]));
    start_article($group,$headers);
    continue;
  }
  # fix lines that started with a period and got escaped
  if (substr($line,0,2) == "..") $line = substr($line,1);
  if ($inheaders) {
    list($k,$v) = explode(": ", $line, 2);
    $headers[strtolower($k)] = $v;
  }
  else {
    # this is some amazingly simplistic code to color quotes/signatures
    # differently, and turn links into real links. it actually appears
    # to work fairly well, but could easily be made more sophistimicated.
    $line = htmlspecialchars($line);
    $line = preg_replace("/((mailto|http|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/","<a href=\"\\1\">\\1</a>\\3",$line);
    if (!$insig && $line == "-- \r\n") {
      echo "<span class=\"signature\">";
      $insig = 1;
    }
    if ($insig && $line == "\r\n") {
      echo "</span>";
      $insig = 0;
    }
    if (!$insig && substr($line,0,4) == "&gt;") {
      echo "<span class=\"quote\">$line</span>";
    }
    else {
      echo $line;
    }
  }
}
if ($insig) echo "</span>";
echo "</pre></blockquote>";

function start_article ($group,$headers) {
  echo "<blockquote>\n";
  echo '<table border="0" cellpadding="2" cellspacing="2" width="100%">';
  # from
  echo '<tr><td class="headerlabel">From:</td><td class="headervalue">'.format_author($headers[from])."</td>\n";
  # date
  echo '<td class="headerlabel">Date:</td><td class="headervalue">'.format_date($headers["date"])."</td></tr>\n";
  # subject
  echo '<tr><td class="headerlabel">Subject:</td><td class="headervalue" colspan="3">'.format_subject($headers["subject"])."</td></tr>\n";
  # references
  if ($headers["references"]) {
    echo '<tr><td class="headerlabel">References:</td><td class="headervalue">';
    $r = explode(" ", $headers["references"]);
    while (list($k,$v) = each($r)) {
      echo "<a href=\"article.php?group=$group&amp;article=".htmlspecialchars(urlencode($v))."\">".($k+1)."</a>\n";
    }
    echo "</td></tr>\n";
  }
  while (list($k,$v) = each($headers)) {
    echo "<!-- $k: $v -->\n";
  }
  echo "</table></blockquote>\n";
  echo "<blockquote><pre>";
}

foot();
