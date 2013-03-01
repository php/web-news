<?php

require 'common.inc.php';
require 'nntp.inc';

if (isset($_GET['article'])) {
	$article = (int)$_GET['article'];
} else {
	error("No article specified");
}

if (isset($_GET['group'])) {
	$group = preg_replace('@[^A-Za-z0-9.-]@', '', $_GET['group']);
} else {
	$group = false;
}

$s = nntp_connect(NNTP_HOST);
if (!$s) {
	error("Failed to connect to news server");
}

if ($group) {
	$res = nntp_cmd($s,"GROUP $group",211);
	if (!$res) {
		error("Failed to select group");
	}
}

$res = nntp_cmd($s, "ARTICLE $article",220);
if (!$res) {
	error("Failed to get article ". $article);
}

$started = 0;
$inheaders = 1; $headers = array();
$masterheaders = null;
$mimetype = $boundary = $charset = $encoding = "";
$mimecount = 0; // number of mime parts
$boundaries = array();
$lk = '';
$linebuf = '';
$insig = false;

//First read into buffer to allow later commands reuse the connection and save resources
$lines=array();
while (!feof($s)) {
	$line=fgets($s);
	$lines[]=$line;
	if($line==".\r\n") break;
}

foreach($lines as $line) {
	if ($line == ".\r\n") break;
	if ($inheaders && ($line == "\n" || $line == "\r\n")) {
		$inheaders = 0;
		$headers=split_headers($headers);
		if (isset($headers['content-type'])) {
			if (isset($headers['content-type']['charset'])) {
				$charset = $headers['content-type']['charset'];
			}
			if (isset($headers['content-type']['boundary'])) {
				$boundaries[] = $headers['content-type']['boundary'];
				$boundary = end($boundaries);
			}
			if ($headers['content-type'][0]
			&& preg_match("/([^;]+)(;|\$)/", $headers['content-type'][0], $m)) {
				$mimetype = trim(strtolower($m[1]));
				++$mimecount;
			}
		}
		if (!$started) {
			head("$group: ".format_title($headers['subject'], $charset));
			start_article($group,$headers,$charset);
			$started = 1;
		}

		$encoding = strtolower(trim(@$headers['content-transfer-encoding']));
		if (strlen($mimetype)
		&& $mimetype != "text/plain"
		&& substr($mimetype,0,10) != "multipart/") {
			# Display a link to the attachment
			$name = '';
			if (isset($headers['content-type']['name'])) {
				$name = $headers['content-type']['name'];
			} else if (isset($headers['content-disposition']['filename'])) {
				$name = $headers['content-disposition']['filename'];
			}

			if ($headers['content-description']) {
				$description = trim($headers['content-description']) . " ";
			} else {
				$description = '';
			}

			$description .= $name;
			$link_desc = "[$mimetype]";
			if (strlen($description)) {
				$link_desc .= " " . $description;
			}

			$dl_link = get_getpart_link($group,$article,$mimecount);
			$link_desc = htmlspecialchars($link_desc,ENT_QUOTES,"UTF-8");
			
			echo "Attachment: <a href=\"$dl_link\">${link_desc}</a><br />\n";
		}

		if ($masterheaders == null) {
			$headers = $masterheaders;
		}
		continue;
	}
	# fix lines that started with a period and got escaped
	if (substr($line,0,2) == "..") {
		$line = substr($line,1);
	}


	if ($inheaders) {
		@list($k,$v) = explode(": ", $line, 2);
		if ($k && $v) {
			$headers[strtolower($k)] = $v;
			$lk = strtolower($k);
		} else {
			$headers[$lk] .= $line;
		}
	}
	else {

		if ($boundary
		&& substr($line,0,2) == '--'
		&& substr($line,2,strlen($boundary)) == $boundary) {

			$inheaders = 1;

			if (substr($line,2+strlen($boundary)) == '--') {
				# end of this container
				array_pop($boundaries);
				$boundary = end($boundaries);
			} else {
				# next section; start with an inherited set of headers
				$headers = $masterheaders;
				$mimetype = "";
			}

			continue;
		}

		if (strlen($mimetype) && $mimetype != "text/plain") {
			continue;
		}

		switch($encoding) {
			case "quoted-printable":
			$line = quoted_printable_decode($line);
			break;
			case "base64":
			$line = base64_decode($line);
			break;
		}

		// we can't convert it to UTF, because cvs commits don't have charset info
		// so its preferable to leave it as-is, and let users choose the correct charset
		// in their browser. this is specially important for php.doc.* groups
		if ($charset && strpos(strtolower($charset), 'utf-8') === false) {
			$line = to_utf8($line, $charset);
		}

		$line = $linebuf . $line;

		if (in_array(substr($line, -1), array("\n", "\r"))) {
		   $linebuf = '';
		} else {
		   $linebuf = $line;
		   continue;
		}

		# this is some amazingly simplistic code to color quotes/signatures
		# differently, and turn links into real links. it actually appears
		# to work fairly well, but could easily be made more sophistimicated.
		$line = htmlentities($line,ENT_NOQUOTES,"utf-8");
		$line = preg_replace("/((mailto|https?|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/","<a href=\"\\1\">\\1</a>\\3",$line);
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
		} else {
			echo $line;
		}
	}
}
if ($inheaders && !$started) {
	head("$group: ". $headers[subject]);
	start_article($group,$headers,$charset);
}
if ($insig) {
	echo "</span>";
}
echo "   </pre>\n";
echo "  </blockquote>\n";

function start_article ($group,$headers,$charset) {
	global $s;
	echo "  <blockquote>\n";
	echo '   <table border="0" cellpadding="2" cellspacing="2" width="100%">' . "\n";
	# from
	echo '    <tr class="vcard">' . "\n";
	echo '     <td class="headerlabel">From:</td>' . "\n";
	echo '     <td class="headervalue">' . format_author($headers['from'], $charset)."</td>\n";
	# date
	echo '     <td class="headerlabel">Date:</td>' . "\n";
	echo '     <td class="headervalue">' . format_date($headers["date"])."</td>\n";
	echo "    </tr>\n";
	# subject
	echo '    <tr>' . "\n";
	echo '     <td class="headerlabel">Subject:</td>' . "\n";
	echo '     <td class="headervalue" colspan="3">'.format_subject($headers["subject"], $charset)."</td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	# references
	if (!empty($headers['references']) || !empty($headers['in-reply-to'])) {
		$ref = $headers["references"] ? $headers["references"] : $headers["in-reply-to"];
		echo '     <td class="headerlabel">References:</td>' . "\n";
		echo '     <td class="headervalue">';
		$ref=str_replace("\t"," ",$ref);
		$r = explode(" ", $ref);
		$c = 1;
		while (list($k,$v) = each($r)) {
			if (!$v) continue;
			$v = trim($v);
			if (!preg_match("/^<.+>\$/", $v)) {
				continue;
			}
			if (strlen($v) > 504) {
				// 512 chars including CRLF
				continue;
			}

			$res2 = nntp_cmd($s, "XPATH $v",223)
			or print("<!-- failed to get reference article id ".htmlspecialchars($v, ENT_QUOTES, "UTF-8")." $v-->");
			list(,$v)  = split("/", trim($res2));
			if (empty($v)) {
				continue;
			}
			echo "<a href=\"".get_article_link($group,$v)."\">".($c++)."</a>&nbsp;";
		}
		echo "</td>\n";
	}
	# groups
	if ($headers["newsgroups"]) {
		echo '     <td class="headerlabel">Groups:</td>' . "\n";
		echo '     <td class="headervalue">';
		$r = explode(",", chop($headers["newsgroups"]));
		while (list($k,$v) = each($r)) {
			echo "<a href=\"".get_group_link($v)."\">".htmlspecialchars($v, ENT_QUOTES, "UTF-8")."</a>&nbsp;";
		}
		echo "</td>\n";
	}
	echo "    </tr>\n";
	//while (list($k,$v) = each($headers)) {
	//  echo "<!-- ", htmlspecialchars($k),": ",preg_replace("/-+/", "-", htmlspecialchars($v))," -->\n";
	//}
	echo "   </table>\n";
	echo "  </blockquote>\n";
	echo "  <blockquote>\n";
	echo "   <pre>\n";
}

// Does not check existence of next, so consider this the super duper fast [broken] version
// Based off navbar() in group.php
function navbar($group, $current) {

	$group = htmlspecialchars($group, ENT_QUOTES, "UTF-8");

	echo '  <table border="0" cellpadding="2" cellspacing="2" width="100%">' . "\n";
	echo '   <tr class="alisthead">' . "\n";
	echo '    <td class="nav">';

	if ($current > 1) {
		echo '     <a href="'.get_article_link($group,($current-1)).'"><b>&laquo; previous</b></a>';
	} else {
		echo '&nbsp;';
	}

	echo '    </td>' . "\n";
	echo '    <td align="center" class="alisthead">' . "$group (#$current)</td>\n";
	echo '    <td align="right" class="nav">';
	echo '     <a href="'.get_article_link($group,($current+1)).'"><b>next &raquo;</b></a>';
	echo '    </td>' . "\n";
	echo '   </tr>' . "\n";
	echo '  </table>' . "\n";
}

navbar($group, $article);
foot();
