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
            echo "    <td class=\"align-center\">" .
                format_date($details['date']) . "</td>\n";
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
}
