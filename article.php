<?php

require 'common.php';
require 'lib/ThreadTree.php';

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
    $nntpClient = new \Web\News\Nntp($NNTP_HOST);
    $message = $nntpClient->readArticle($article, $group);

    if ($message === null) {
        error('No article found');
    }

    $mail = \Flourish\Mailbox::parseMessage($message);

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
echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/' .
    htmlspecialchars($group, ENT_QUOTES, "UTF-8") . '">' .
    htmlspecialchars($group, ENT_QUOTES, "UTF-8") . '</a></li>';
echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/' .
    htmlspecialchars($group, ENT_QUOTES, "UTF-8") . '/' . $article . '">' .
    format_title($mail['headers']['subject'], 'utf-8') . '</a></li>';
echo ' </ul>';
echo '</nav>';
echo '<section class="content">';

echo '<h1>' . format_subject($mail['headers']['subject'], 'utf-8') . "</h1>\n";

echo "  <blockquote>\n";
echo '   <table class="standard">' . "\n";
# from
echo '    <tr class="vcard">' . "\n";
echo '     <td class="headerlabel">From:</td>' . "\n";
echo '     <td class="headervalue">' . format_author($mail['headers']['from']['raw'], 'utf-8') . "</td>\n";
# date
echo '     <td class="headerlabel">Date:</td>' . "\n";
echo '     <td class="headervalue">' . format_date($mail['headers']['date']) . "</td>\n";
echo "    </tr>\n";
# subject
echo '    <tr>' . "\n";
echo '     <td class="headerlabel">Subject:</td>' . "\n";
echo '     <td class="headervalue" colspan="3">' . format_subject($mail['headers']['subject'], 'utf-8') . "</td>\n";
echo "    </tr>\n";
echo "    <tr>\n";
# references
if (!empty($refsResolved)) {
    echo '     <td class="headerlabel">References:</td>' . "\n";
    echo '     <td class="headervalue" ' . (empty($mail['headers']['newsgroups']) ? 'colspan="3"' : null) . '>';
    foreach ($refsResolved as $k => $ref) {
        echo "<a href=\"/" . urlencode($ref['group']) . '/' . urlencode($ref['articleId']) . "\">" .
            ($k + 1) . "</a>&nbsp;";
    }
    echo "</td>\n";
}
# groups
if (!empty($mail['headers']['newsgroups'])) {
    echo '     <td class="headerlabel">Groups:</td>' . "\n";
    echo '     <td class="headervalue" ' . (empty($refsResolved) ? 'colspan="3"' : null) . '>';
    $r = explode(",", rtrim($mail['headers']['newsgroups']));
    foreach ($r as $v) {
        echo "<a href=\"/" . urlencode($v) . "\">" . htmlspecialchars($v) . "</a>&nbsp;";
    }
    echo "</td>\n";
}
echo "    </tr>\n";
# email to request archived copy
$request_address = get_list_address($group) . '+get-' . $article . '@lists.php.net';
echo '    <tr>' . "\n";
echo '     <td class="headerlabel">Request:</td>' . "\n";
echo '     <td class="headervalue" colspan="3">Send a blank email to <a href="mailto:' . clean($request_address) . '">' . clean($request_address)  . "</a> to get a copy of this message</td>\n";
echo "    </tr>\n";
echo "    <tr>\n";
echo "   </table>\n";
echo "  </blockquote>\n";
echo "  <blockquote>\n";
$class = $mail['flowed'] ? ' class="flowed"' : '';
echo "   <pre$class>\n";

/*
 * If there was no text part of the message, see what we can do about creating
 * one from a text/html part, or just inject a note that there was no text to
 * avoid further errors.
 */
if (!array_key_exists('text', $mail)) {
    if (array_key_exists('html', $mail)) {
        /*
         * This just aggressively strips out all tags. For the examples at
         * hand, this looked okay-ish. Better than nothing, at least, and
         * should be totally safe because all of the text get re-encoded
         * later.
         */
        // This makes HTML from Fastmail retain paragraph breaks
        $text = preg_replace('#<div><br></div>#', "\n\n", $mail['html']);
        // And this avoids extra linebreaks from another example (Android?)
        $text = preg_replace("#\n<br>\n#", "\n", $text);
        $mail['text'] = html_entity_decode(strip_tags($text), encoding: 'UTF-8');
    } else {
        $mail['text'] = "> There was no text content in this message.";
    }
}

$lines = preg_split("@(?<=\r\n|\n)@", $mail['text']);
$insig = $is_commit = $is_diff = 0;
$level = 0;
$in_flow = $was_flowed = false;
$in_code_block = false;

foreach ($lines as $line) {
    # Trim end of line
    $line = preg_replace('/\r?\n$/', '', $line);

    # fix lines that started with a period and got escaped
    if (str_starts_with($line, "..")) {
        $line = substr($line, 1);
    }

    # Notice commit messages so we can highlight the diffs
    if (str_starts_with($line, 'Commit: https://github.com/php')) {
        $is_commit = 1;
    }

    # We don't use htmlentities() because it seems like overkill and that
    # makes all of the later substitutions more complicated.
    $line = htmlspecialchars($line, ENT_QUOTES|ENT_SUBSTITUTE|ENT_HTML5, "utf-8");

    # Turn bare links, not within [] or (), to HTML links
    $line = preg_replace(
        "/(^|[^[(])((mailto|https?|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/",
        "\\1<a href=\"\\2\">\\2</a>\\4",
        $line
    );

    # Turn Markdown links to HTML links
    $line = preg_replace(
        "/\[((mailto|https?|ftp|nntp|news):.+?)\]\((.+?)\)/",
        "<a href=\"\\1\">\\3</a>",
        $line
    );

    # Highlight inline code
    $line = preg_replace(
        "/`([^`]+?)`/",
        "<code>\\1</code>",
        $line
    );

    # Begin signature when we see the tell-tale '-- ' line
    if (!$insig && $line == "-- ") {
        echo "<span class=\"signature\">";
        $insig = 1;
    }

    # In commit messages, highlight lines that start with + or -
    if (!$insig && $is_commit && preg_match('/^[-+]/', $line, $m)) {
        $is_diff = 1;
        echo '<span class="' . ($m[0] == '+' ? 'added' : 'removed') . '">';
    }

    # This gets a little tricker -- "flowed" messages basically have long
    # quoted lines broken up, so we can put quoted blocks in levels of <div>
    # blocks instead of highlighting them per-line

    if (!$insig && $mail['flowed']) {
        $flowed = false;
        $new_level = 0;

        if (preg_match('/^((\s*&gt;)+)(.*)/', $line, $m)) {
            $new_level = substr_count($m[1], $m[2]);
            $line = $m[3];
        }

        # Trim leading space (a format=flowed thing)
        if (str_starts_with($line, ' ')) {
            $line = substr($line, 1);
        }

        # A "flowed" line ends with a space. We also remove it if DelSp = "Yes".
        if (str_ends_with($line, ' ')) {
            $flowed = true;
            if ($mail['delsp']) {
                $line = substr($line, 0, -1);
            }
        }

        # If this line had more quoting, go ahead and open to that level
        if ($new_level && $new_level > $level) {
            foreach (range($level + 1, $new_level) as $this_level) {
                echo "<div class=\"quote quote{$this_level}\">";
            }
            $level = $new_level;
            $in_flow = true;
        }
        # Otherwise if we are in a flow, but this line's level is lower (but
        # not 0), we need to close up the higher levels
        elseif ($in_flow && $new_level && $new_level < $level) {
            echo str_repeat('</div>', $level - $new_level);
            $level = $new_level;
        }

        # Handle indented code blocks
        if (preg_match('/( |\xC2\xA0){4}/', $line)) {
            if (!$in_code_block) {
                echo '<pre>';
                $in_code_block = true;
            }
        } elseif (!$flowed && !$was_flowed) {
            if ($in_code_block && is_bool($in_code_block)) {
                echo '</pre>';
                $in_code_block = false;
            }
        }

        # Handle ``` delimited code blocks
        if (preg_match('/^```(\w+)?$/', $line, $m)) {
            if ($in_code_block) {
                echo '</pre>';
                $in_code_block = false;
                continue;
            } else {
                $language = $m[1] ?? 'php';
                echo "<pre class=\"language_{$language}\">";
                $in_code_block = $language;
                continue;
            }
        }

        # Hey, it's the actual line of text!
        echo $line;

        # If the line is fixed, we close a flow or just add a newline
        if (!$flowed) {
            if ($level != $new_level) {
                # Close out code block if we were in one
                if ($in_code_block) {
                    echo '</pre>';
                    $in_code_block = false;
                }
                echo str_repeat("</div>", $level) . "\n";
                $level = 0;
                $in_flow = false;
            } else {
                echo "\n";
            }
        }

        $was_flowed = $flowed;
    }
    # Otherwise we're in a signature or not flowed
    else {
        if (!$insig && preg_match('/^((\s*\w*?&gt; ?)+)/', $line, $m)) {
            $level = substr_count($m[1], '&gt;') % 4;
            echo "<span class=\"quote$level\">", wordwrap($line, 100, "\n" . $m[1]), "</span>";
        } else {
            echo wordwrap($line, 100);
        }
        echo "\n";
    }

    # If this line was a diff, close out the <span>
    if ($is_diff) {
        $is_diff = 0;
        echo '</span>';
    }
}

if ($in_code_block) {
    echo '</pre>';
}
if ($insig) {
    echo "</span>";
    $insig = 0;
}
if ($mail['flowed'] && $level) {
    echo str_repeat('</div>', $level);
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
        $link_desc = htmlspecialchars($link_desc, ENT_QUOTES, 'UTF-8');

        /* Attachment filename and mimetype might contain malicious characters */
        printf(
            'Attachment: <a href="%s">%s</a><br />' . "\n",
            $dl_link,
            htmlspecialchars($link_desc)
        );
    }
}

echo "   </pre>\n";
echo "  </blockquote>\n";

try {
    $overview = $nntpClient->getThreadOverview($group, $article);

    $threads = new \PhpWeb\ThreadTree($overview['articles']);
    ?>
      <blockquote>
        <h2>
          Thread (<?= sprintf("%d message%s", $count = $threads->count(), $count > 1 ? 's' : '') ?>)
        </h2>
        <?php $threads->printFullThread($group, $article, charset: 'utf8'); ?>
      </blockquote>
    <?php
} catch (\Throwable $t) {
    // We don't care if there's no thread. (There should be, though.)
}

// Does not check existence of next, so consider this the super duper fast [broken] version
// Based off navbar() in group.php
$group = htmlspecialchars($group, ENT_QUOTES, "UTF-8");
$current = $article;

echo '  <table class="standard">' . "\n";
echo '   <tr>' . "\n";
echo '    <th class="nav">';

if ($current > 1) {
    echo '     <a href="/' , $group , '/' , ($current - 1) , '"><b>&laquo; <span>previous</span></b></a>';
} else {
    echo '&nbsp;';
}

echo '    </th>' . "\n";
echo '    <th class="align-center">' . "$group (#$current)</th>\n";
echo '    <th class="nav align-right">';
echo '     <a href="/' , $group , '/' , ($current + 1) , '"><b><span>next</span> &raquo;</b></a>';
echo '    </th>' . "\n";
echo '   </tr>' . "\n";
echo '  </table>' . "\n";
echo '</section>';

foot();
