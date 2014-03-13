<?php

require 'common.php';

if (isset($_GET['group'])) {
	$group = preg_replace('@[^A-Za-z0-9.-]@', '', $_GET['group']);
} else {
	error("Missing group");
}

if (isset($_GET['format'])) {
	$format = $_GET['format'];
} else {
	// assume html
	$format = 'html';
}

if (isset($_GET['i'])) {
	$i = (int)$_GET['i'];
} else {
	$i = 0;
}

$s = nntp_connect(NNTP_HOST);
if (!$s) {
	error("Failed to connect to news server");
}

$res = nntp_cmd($s,"GROUP $group",211);
if (!$res) {
	error("Failed to get info on group");
}

list (, $f, $l, $g) = explode(" ", $res);
if (!$i || $i > $l - 19 || $i < $f) {
	$i = $l - $f > 19 ? $l - 19 : $f;
}
$n = min($l, $i + 19);

$res = nntp_cmd($s,"XOVER $i-$n", 224);
if (!$res) {
	error("Failed to get xover data");
}

$host = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, "UTF-8");
switch($format) {
	case 'rss':
	header("Content-type: text/xml");
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";?>
<rss version="0.93">
 <channel> 
  <title><?php echo $host; ?>: <?php echo $group?></title>
  <link>http://<?php echo $host; ?>/group.php?group=<?php echo $group?></link>
  <description></description>
<?php  break;
case 'rdf':
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<rdf:RDF
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns="http://my.netscape.com/rdf/simple/0.9/">
 <channel>
  <title><?php echo $host; ?>: <?php echo $group?></title>
  <link>http://<?php echo $host; ?>/group.php?group=<?php echo $group?></link>
  <description><?php echo $group?> Newsgroup at <?php echo NNTP_HOST; ?></description>
  <language>en-US</language>
 </channel>
<?php
break;
case 'html':
default:
head($group);
navbar($group,$f,$l,$i);
echo '  <table class="alist" width="100%">' . "\n";
echo '   <tr>' . "\n";
echo '    <td class="alisthead">#</td>' . "\n";
echo '    <td class="alisthead">subject</td>' . "\n";
echo '    <td class="alisthead">author</td>' . "\n";
echo '    <td class="alisthead">date</td>' . "\n";
echo '    <td class="alisthead">lines</td>' . "\n";
echo '   </tr>' . "\n";
break;
}

# list of articles
$class = "even";
# TODO: somehow determine the correct charset
$charset = "";

while ($line = fgets($s, 16384)) {
	if ($line == ".\r\n") break;
	$line = chop($line);
	list($n,$subj,$author,$odate,$messageid,$references,$bytes,$lines,$extra)
	= explode("\t", $line, 9);
	/*  $date = date("H:i:s M/d/y", strtotime($odate)); */
	$date822 = date("r", strtotime($odate));

	switch($format) {
		case 'rss':
		echo "  <item>\n";
		echo "   <link>http://$host/$group/$n</link>\n";
		echo "   <title>", format_subject($subj, $charset), "</title>\n";
		echo "   <description>", htmlspecialchars(format_author($author, $charset), ENT_QUOTES, "UTF-8"), "</description>\n";
		echo "   <pubDate>$date822</pubDate>\n";
		echo "  </item>\n";
		break;
		case 'rdf':
		echo " <item>\n";
		echo "  <title>", format_subject($subj, $charset), "</title>\n";
		echo "  <link>http://$host/$group/$n</link>\n";
		echo "  <description>", htmlspecialchars(format_author($author, $charset), ENT_QUOTES, "UTF-8"), "</description>\n";
		echo "  <pubDate>$date822</pubDate>\n";
		echo " </item>\n";
		break;
		case 'html':
		default:
		echo "   <tr>\n";
		echo "    <td class=\"$class\"><a href=\"/$group/$n\">$n</a></td>\n";
		echo "    <td class=\"$class\"><a href=\"/$group/$n\">";
		echo format_subject($subj, $charset);
		echo "</a></td>\n";
		echo "    <td class=\"$class vcard\">".format_author($author, $charset)."</td>\n";
		echo "    <td align=\"center\" class=\"$class\"><tt>" . format_date($odate) . "</tt></td>\n";
		echo "    <td align=\"right\" class=\"$class\">$lines</td>\n";
		echo "   </tr>\n";
	}
	$class = ($class == "even") ? "odd" : "even";
}

switch ($format) {
	case 'rss':
	echo " </channel>\n</rss>\n";
	break;
	case 'rdf':
	echo "</rdf:RDF>\n";
	break;
	case 'html':
	default:
	echo "  </table>\n";
	navbar($group, $f, $l, $i);
	foot();
}

function navbar($g, $f, $l, $i) {
	echo '  <table border="0" cellpadding="2" cellspacing="2" width="100%">' . "\n";
	echo '   <tr class="alisthead">' . "\n";
	echo '    <td class="nav">';
	if ($i > $f) {
		$p = max($i-20,$f);
		echo "<a href=\"/" . htmlspecialchars($g, ENT_QUOTES, "UTF-8") . "/start/$p\"><b>&laquo; previous</b></a>";
	} else {
		echo "&nbsp;";
	}
	echo '</td>' . "\n";
	$j = min($i + 20, $l);
	$c = $l - $f + 1;
	echo '    <td align="center" class="alisthead">'.htmlspecialchars($g, ENT_QUOTES, "UTF-8")." ($i-$j of $c)</td>\n";
	echo '    <td align="right" class="nav">';
	if ($i+20 <= $l) {
		$n = min($i + 20, $l - 19);
		echo "<a href=\"/" . htmlspecialchars($g, ENT_QUOTES, "UTF-8") . "/start/$n\"><b>next &raquo;</b></a>";
	}
	else {
		echo "&nbsp;";
	}
	echo '</td>' . "\n";
	echo '   </tr>' . "\n";
	echo '  </table>' . "\n";
}
