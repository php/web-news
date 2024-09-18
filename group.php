<?php

require 'common.php';
require 'lib/group-navbar.php';

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

try {
    $nntpClient = new \Web\News\Nntp($NNTP_HOST);
    $overview = $nntpClient->getArticlesOverview($group, $i);
} catch (Exception $e) {
    error($e->getMessage());
}

$host = htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, "UTF-8");
switch ($format) {
    case 'rss':
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";?>
<rss version="2.0">
 <channel> 
  <title><?php echo $host; ?>: <?php echo $group?></title>
  <link>http://<?php echo $host; ?>/group.php?group=<?php echo $group?></link>
  <description></description>
        <?php
        break;
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
  <description><?php echo $group?> Newsgroup at <?php echo $NNTP_HOST; ?></description>
  <language>en-US</language>
 </channel>
        <?php
        break;
    case 'html':
    default:
        head($group . ' mailing list');
        echo '<nav class="secondary-nav">';
        echo ' <ul class="breadcrumbs">';
        echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/">PHP Mailing Lists</a></li>';
        echo '  <li class="breadcrumbs-item"><a class="breadcrumbs-item-link" href="/',
            htmlspecialchars($group, ENT_QUOTES, "UTF-8") . '">',
            htmlspecialchars($group, ENT_QUOTES, "UTF-8") . '</a></li>';
        echo ' </ul>';
        echo '</nav>';
        echo '<section class="content">';
        echo '<h1>' . htmlspecialchars($group, ENT_QUOTES, "UTF-8") . '</h1>';
        if ($i == 0) {
            /* Special header of info for the main page for a group */
            $groups = $nntpClient->listGroups($group);

            if ($groups[$group]['status'] == 'n') {
                ?>
                    <p class="warning">
                        <strong>Warning:</strong> This list is closed to new
                        posts and subscribers.
                    </p>
                <?php
            } else {
                if ($groups[$group]['status'] == 'm') {
                    ?>
                        <p class="warning">
                            <strong>Note:</strong> This list is only for
                            announcements, so only approved posters can send messages.
                        </p>
                    <?php
                }
                $subscription_address = htmlspecialchars(get_subscribe_address($group));
                ?>
                    <form class="subscription-form" method="POST" action="/subscribe.php">
                        <input type="hidden" name="group" value="<?= htmlspecialchars($group) ?>">
                        <div>
                            <label for="email">Email address:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div>
                            <label>
                                <input type="radio" name="type" value="" checked>
                                Standard
                            </label>
                            <label>
                                <input type="radio" name="type" value="digest">
                                Digest
                            </label>
                            <label>
                                <input type="radio" name="type" value="nomail">
                                No emails
                            </label>
                        </div>
                        <div>
                            <button type="submit">
                                Subscribe
                            </button>
                        </div>
                    </form>
                    <p>
                        You can also subscribe to this list by sending a blank email to
                        <a href="mailto:<?= $subscription_address ?>">
                          <?= $subscription_address ?>
                        </a>
                    </p>
                <?php
            }
        }
        navbar($group, $overview['group']['low'], $overview['group']['high'], $overview['group']['start']);
        echo ' <div class="responsive-table">' . "\n";
        echo '  <table class="standard">' . "\n";
        echo '   <tr>' . "\n";
        echo '    <th>#</th>' . "\n";
        echo '    <th>subject</th>' . "\n";
        echo '    <th>author</th>' . "\n";
        echo '    <th>date</th>' . "\n";
        echo '    <th>lines</th>' . "\n";
        echo '   </tr>' . "\n";
        break;
}

# list of articles
# TODO: somehow determine the correct charset
$charset = "utf-8";

foreach ($overview['articles'] as $articleNumber => $details) {
    /*  $date = date("H:i:s M/d/y", strtotime($odate)); */
    $date822 = date("r", strtotime($details['date']));

    switch ($format) {
        case 'rss':
            echo "  <item>\n";
            echo "   <link>http://$host/$group/$articleNumber</link>\n";
            echo "   <title>", format_subject($details['subject'], $charset), "</title>\n";
            echo "   <description>",
                htmlspecialchars(format_author($details['author'], $charset), ENT_QUOTES, "UTF-8"),
                "</description>\n";
            echo "   <pubDate>$date822</pubDate>\n";
            echo "  </item>\n";
            break;
        case 'rdf':
            echo " <item>\n";
            echo "  <title>", format_subject($details['subject'], $charset), "</title>\n";
            echo "  <link>http://$host/$group/$articleNumber</link>\n";
            echo "  <description>",
                htmlspecialchars(format_author($details['author'], $charset), ENT_QUOTES, "UTF-8"),
                "</description>\n";
            echo "  <pubDate>$date822</pubDate>\n";
            echo " </item>\n";
            break;
        case 'html':
        default:
            echo "   <tr>\n";
            echo "    <td><a href=\"/$group/$articleNumber\">$articleNumber</a></td>\n";
            echo "    <td><a href=\"/$group/$articleNumber\">";
            echo format_subject($details['subject'], $charset);
            echo "</a></td>\n";
            echo "    <td class=\"vcard\">" . format_author($details['author'], $charset) . "</td>\n";
            echo "    <td class=\"align-center\"><span class='monospace mod-small'>" .
                format_date($details['date']) . "</span></td>\n";
            echo "    <td class=\"align-right\">{$details['lines']}</td>\n";
            echo "   </tr>\n";
    }
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
        echo " </div>\n";
        navbar($group, $overview['group']['low'], $overview['group']['high'], $overview['group']['start']);
        echo "</section>";
        foot();
}
