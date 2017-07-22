<?php

require 'common.php';

/* Prevents the poor mail server from suffering if it receives a message with many references */
/* (References: <xxx> or In-Reply-To: <xxx>) */
define('REFERENCES_LIMIT', 20);

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

try {
	$nntpClient = new \Web\News\Nntp(NNTP_HOST);
	$message = $nntpClient->readArticle($article, $group);

	if ($message === null) {
		error('No article found');
	}

	$mail = fMailbox::parseMessage($message);

	$rawReferences = [];
	if (!empty($mail['headers']['references'])) {
		$rawReferences = $mail['headers']['references'];
	} elseif (!empty($mail['headers']['in-reply-to'])) {
		$rawReferences = $mail['headers']['in-reply-to'];
	}

	$references = [];
	foreach ($rawReferences as $ref) {
		$matches = [];
		if (preg_match_all('/\<(.*?)\>/', $ref, $matches)) {
			foreach ($matches[0] as $match) {
				$references[] = $match;
			}
		}
	}

	$refsResolved = [];

	$refCount = 0;
	foreach ($references as $messageId) {
		if (!$messageId) {
			continue;
		}
		if ($refCount >= REFERENCES_LIMIT) {
			break;
		}
		$refsResolved[] = $nntpClient->xpath($messageId);
		$refCount++;
	}
} catch (Exception $e) {
	error($e->getMessage());
}

head("{$group}: " . format_title($mail['headers']['subject'], 'utf-8'));
echo '<nav class="secondary-nav">';
echo ' <ul class="breadcrumbs">';
echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/">PHP Mailing Lists</a></li>';
echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/'.htmlspecialchars($group, ENT_QUOTES, "UTF-8").'">'.htmlspecialchars($group, ENT_QUOTES, "UTF-8").'</a></li>';
echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/'.htmlspecialchars($group, ENT_QUOTES, "UTF-8").'/'.$article.'">'.format_title($mail['headers']['subject']).'</a></li>';
echo ' </ul>';
echo '</nav>';
echo '<section class="content">';
start_article($mail, $refsResolved);

$lines = preg_split("@(?<=\r\n|\n)@", $mail['text']);
$insig = 0;

foreach ($lines as $line) {
	# fix lines that started with a period and got escaped
	if (substr($line,0,2) == "..") {
		$line = substr($line,1);
	}

	# this is some amazingly simplistic code to color quotes/signatures
	# differently, and turn links into real links. it actually appears
	# to work fairly well, but could easily be made more sophistimicated.
	/* NOQUOTES? Why? It creates invalid HTML: http:"x */
	$line = htmlentities($line,ENT_QUOTES,"utf-8");
	$line = preg_replace("/((mailto|https?|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/","<a href=\"\\1\">\\1</a>\\3",$line);
	if (!$insig && ($line == "-- \r\n" || $line == "--\r\n")) {
		echo "<span class=\"signature\">";
		$insig = 1;
	}
	if (!$insig && substr($line,0,4) == "&gt;") {
		echo "<span class=\"quote\">$line</span>";
	} else {
		echo $line;
	}
}

if ($insig) {
	echo "</span>";
	$insig = 0;
}

echo "<br><br>";

if (!empty($mail['attachment'])) {
	foreach ($mail['attachment'] as $mimecount => $attachment) {
		$mimetype = $attachment['mimetype'];
		$name = $attachment['filename'];

		if ($mimetype == 'text/plain') {
			echo htmlspecialchars($attachment['data']);
			continue;
		}

		if (!empty($attachment['description'])) {
			$description = trim($attachment['description']) . " ";
		} else {
			$description = '';
		}

		$description .= $name;
		$link_desc = "[$mimetype]";
		if (strlen($description)) {
			$link_desc .= " " . $description;
		}

		$dl_link = "/getpart.php?group=$group&amp;article=$article&amp;part=$mimecount";
		$link_desc = htmlspecialchars($link_desc,ENT_QUOTES,'UTF-8');

		/* Attachment filename and mimetype might contain malicious characters */
		printf('Attachment: <a href="%s">%s</a><br />'."\n",
			$dl_link,
			htmlspecialchars($link_desc)
		);
	}
}

echo "   </pre>\n";
echo "  </blockquote>\n";

function start_article($mail, $refsResolved) {

    echo '<h1>'.format_subject($mail['headers']['subject'], 'utf-8')."</h1>\n";

	echo "  <blockquote>\n";
	echo '   <table class="standard">' . "\n";
	# from
	echo '    <tr class="vcard">' . "\n";
	echo '     <td class="headerlabel">From:</td>' . "\n";
	echo '     <td class="headervalue">' . format_author($mail['headers']['from']['raw'], 'utf-8')."</td>\n";
	# date
	echo '     <td class="headerlabel">Date:</td>' . "\n";
	echo '     <td class="headervalue">' . format_date($mail['headers']['date'])."</td>\n";
	echo "    </tr>\n";
	# subject
	echo '    <tr>' . "\n";
	echo '     <td class="headerlabel">Subject:</td>' . "\n";
	echo '     <td class="headervalue" colspan="3">'.format_subject($mail['headers']['subject'], 'utf-8')."</td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	# references
	if (!empty($refsResolved)) {
		echo '     <td class="headerlabel">References:</td>' . "\n";
		echo '     <td class="headervalue" '.(empty($mail['headers']['newsgroups']) ? 'colspan="3"' : null).'>';
		foreach ($refsResolved as $k => $ref) {
			echo "<a href=\"/". urlencode($ref['group']) . '/' . urlencode($ref['articleId']) ."\">".($k + 1)."</a>&nbsp;";
		}
		echo "</td>\n";
	}
	# groups
	if (!empty($mail['headers']['newsgroups'])) {
		echo '     <td class="headerlabel">Groups:</td>' . "\n";
		echo '     <td class="headervalue" '.(empty($refsResolved) ? 'colspan="3"' : null).'>';
		$r = explode(",", rtrim($mail['headers']['newsgroups']));
		while (list($k,$v) = each($r)) {
			echo "<a href=\"/".urlencode($v)."\">".htmlspecialchars($v)."</a>&nbsp;";
		}
		echo "</td>\n";
	}
	echo "    </tr>\n";
	echo "   </table>\n";
	echo "  </blockquote>\n";
	echo "  <blockquote>\n";
	echo "   <pre>\n";
}

// Does not check existence of next, so consider this the super duper fast [broken] version
// Based off navbar() in group.php
function navbar($group, $current) {

	$group = htmlspecialchars($group, ENT_QUOTES, "UTF-8");

	echo '  <table class="standard">' . "\n";
	echo '   <tr>' . "\n";
	echo '    <th class="nav">';

	if ($current > 1) {
		echo '     <a href="/' , $group , '/' , ($current-1) , '"><b>&laquo; <span>previous</span></b></a>';
	} else {
		echo '&nbsp;';
	}

	echo '    </th>' . "\n";
	echo '    <th class="align-center">' . "$group (#$current)</th>\n";
	echo '    <th class="nav align-right">';
	echo '     <a href="/' , $group , '/' , ($current+1) , '"><b><span>next</span> &raquo;</b></a>';
	echo '    </th>' . "\n";
	echo '   </tr>' . "\n";
	echo '  </table>' . "\n";
}

navbar($group, $article);
echo '</section>';
foot();
