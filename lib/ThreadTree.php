<?php

namespace PhpWeb;

class ThreadTree
{
    public $articles;
    public $root;
    public $tree = [];
    public $articleNumbers = [];
    public $extraRootChildren = [];

    public function __construct(array $articles)
    {
        $this->articles = $articles;

        /*
         * We need to build a tree of the articles. We know they are in article
         * number order, we assume that this means that parents come before
         * children. There may end up being some posts that appear unattached
         * and we just assume they are replies to the root of the tree.
         */
        foreach ($this->articles as $articleNumber => $details) {
            $messageId = $details['messageId'];

            if (!isset($this->root)) {
                $this->root = $messageId;
            }

            $this->articleNumbers[$messageId] = $articleNumber;

            if ($details['references']) {
                /* Parent is the last reference. */
                if (preg_match('/.*(<.+?>)$/', $details['references'], $matches)) {
                    $parent = $matches[1];
                    $this->tree[$parent][] = $messageId;
                    if (!array_key_exists($parent, $this->articleNumbers)) {
                        $this->extraRootChildren[] = $parent;
                    }
                }
            } else {
                if ($this->root && $this->root != $messageId) {
                    $this->extraRootChildren[] = $messageId;
                }
            }
        }
    }

    public function count()
    {
        return count($this->articleNumbers);
    }

    protected function printArticleAndChildren($messageId, $group, $charset, $depth = 0)
    {
        if (array_key_exists($messageId, $this->articleNumbers)) {
            $articleNumber = $this->articleNumbers[$messageId];

            # for debugging that we've actually handled all articles
            #unset($this->articleNumbers[$messageId]);

            $details = $this->articles[$articleNumber];

            echo "   <tr>\n";
            echo "    <td align=\"center\"><a href=\"/$group/$articleNumber\">$articleNumber</a></td>\n";
            echo "    <td>";
            echo str_repeat("&nbsp; &nbsp;", $depth ?? 0);
            echo "<a href=\"/$group/$articleNumber\">";
            echo format_subject($details['subject'], $charset);
            echo "</a></td>\n";
            echo "    <td class=\"vcard\">" . format_author($details['author'], $charset) . "</td>\n";
            echo "    <td class=\"align-center\"><span class='monospace mod-small'>" .
                format_date($details['date']) . "</span></td>\n";
            echo "   </tr>\n";
        }

        // bail out if things are too deep
        if ($depth > 40) {
            error_log("Tree was too deep, didn't print children of {$messageId})");
            return;
        }

        if (array_key_exists($messageId, $this->tree)) {
            foreach ($this->tree[$messageId] as $child) {
                $this->printArticleAndChildren($child, $group, $charset, $depth + 1);
            }
        }
    }

    public function printRows($group, $charset = 'utf8')
    {
        $this->printArticleAndChildren($this->root, $group, $charset);
        foreach ($this->extraRootChildren as $root) {
            $this->printArticleAndChildren($root, $group, $charset, 1);
        }
    }

    public function printFullThread(
        $group,
        $includingArticleNumber,
        $charset = null
    ) {
        echo "<div class=\"list-tree\"><ul>";
        $this->printThread(
            group: $group,
            messageId: $this->root,
            activeArticleNumber: $includingArticleNumber,
            charset: $charset,
        );

        foreach ($this->extraRootChildren as $childMessageId) {
            $this->printThread(
                group: $group,
                activeArticleNumber: $includingArticleNumber,
                messageId: $childMessageId,
                charset: $charset,
            );
        }

        echo "</ul></div>";
    }

    public function printThread(
        $group,
        $messageId = null,
        $activeArticleNumber = null,
        $depth = 0,
        $subject = "",
        $charset = 'utf8'
    ) {
        if ($depth > 40) {
            echo "<li>Too deep!</li>";
            return;
        }

        if (array_key_exists($messageId, $this->articleNumbers)) {
            $articleNumber = $this->articleNumbers[$messageId];

            # for debugging that we've actually handled all articles
            #unset($this->articleNumbers[$messageId]);

            $details = $this->articles[$articleNumber];

            echo '<li>';

            $details = $this->articles[$articleNumber];

            if ($articleNumber != $activeArticleNumber) {
                echo "<a href=\"/$group/$articleNumber\">";
            } else {
                echo "<b>";
            }
            echo
                '<span class="author">',
                format_author($details['author'], $charset, nameOnly: true),
                '</span>',
                '<span class="date">',
                '<time datetime="', format_date($details['date'], 'c'), '">',
                format_date($details['date'], "D, j M Y H:i"),
                '</time>',
                '</span>';

            $newSubject = format_subject($details['subject'], $charset, trimRe: true);
            if ($messageId != $this->root && $newSubject != $subject) {
                echo '<span class="subject">';
                echo format_subject($details['subject'], $charset);
                echo '</span>';
            }

            if ($articleNumber != $activeArticleNumber) {
                echo "</a>";
            } else {
                echo "</b>";
            }

            if (array_key_exists($messageId, $this->tree)) {
                echo '<ul>';
                foreach ($this->tree[$messageId] as $childMessageId) {
                    $this->printThread(
                        group: $group,
                        activeArticleNumber: $activeArticleNumber,
                        messageId: $childMessageId,
                        subject: $newSubject,
                        charset: $charset,
                        depth: $depth + 1,
                    );
                }
                echo '</ul>';
            }

            echo "</li>";
        }
    }
}
