<?php

require 'common.php';

try {
    $nntpClient = new \Web\News\Nntp($NNTP_HOST);
    $groups = $nntpClient->listGroups();
    $descriptions = $nntpClient->listGroupDescriptions();
    /* Reorder so it's moderated, active, and inactive */
    $order = [ 'm' => 1, 'y' => 2, 'n' => 3 ];
    uasort($groups, function ($a, $b) use ($order) {
        return $order[$a['status']] <=> $order[$b['status']];
    });
} catch (Exception $e) {
    error($e->getMessage());
}

head();

$DISPLAY_NNTP_HOST = htmlspecialchars(($NNTP_HOST == 'localhost') ? 'news.php.net' : $NNTP_HOST);
?>

<nav class="secondary-nav">
 <ul class="breadcrumbs">
  <li><a class="breadcrumbs-item-link" href="/">PHP Mailing Lists</a></li>
 </ul>
</nav>

 <section class="content">

  <div class="welcome">
   <h1>PHP Mailing Lists</h1>
   <p>
    The PHP project collaborates across a number of mailing lists. The archives
    are available through this site and via NNTP at
    <a href="news://<?= $DISPLAY_NNTP_HOST ?>"> <?= $DISPLAY_NNTP_HOST ?></a>.
   </p>
   <p>
    Instructions for subscribing to active lists by email can be found on the page
    for each list (just follow the links below). Participation on each list is governed
    by the <a href="https://github.com/php/php-src/blob/master/docs/mailinglist-rules.md">
    mailing list rules</a>.
   </p>
  </div>
  <table class="standard">
   <tr>
    <th>Name</th>
    <th>Description</th>
    <th>Messages</th>
    <th>RSS</th>
   </tr>
   <tr>
    <th colspan="4">Moderated Lists</th>
   </tr>
<?php
$last_status = 'm';
foreach ($groups as $group => $details) {
    if ($details['status'] != $last_status) {
        $last_status = $details['status'];
        echo '<tr><th colspan="4">',
            $last_status == 'y' ? 'Discussion Lists' : 'Inactive Lists',
            "</th></tr>\n";
    }
    echo "       <tr>\n";
    echo "        <td><a class=\"active{$details['status']}\" href=\"/$group\">$group</a></td>\n";
    echo "        <td>", htmlspecialchars($descriptions[$group]), "</td>\n";
    echo "        <td class=\"align-right\">", $details['high'] - $details['low'] + 1, "</td>\n";
    echo "        <td class=\"align-center\">";
    if ($details['status'] != 'n') {
        echo "<a href=\"group.php?group=$group&amp;format=rss\">RSS</a>";
    }
    echo "</td>\n";
    echo "       </tr>\n";
}
?>
  </table>
 </section>
<?php

foot();
