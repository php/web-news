<?php

require 'common.php';

try {
    $nntpClient = new \Web\News\Nntp($NNTP_HOST);
    $groups = $nntpClient->listGroups();
    /* Reorder so it's moderated, active, and inactive */
    $order = [ 'm' => 1, 'y' => 2, 'n' => 3 ];
    uasort($groups, function ($a, $b) use ($order) {
        return $order[$a['status']] <=> $order[$b['status']];
    });
} catch (Exception $e) {
    error($e->getMessage());
}

head();

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
    This is a completely experimental interface to the PHP mailing lists as
    reflected on the <a href="news://news.php.net/">news.php.net NNTP server</a>.
    The news server software that is used is <a
      href="https://trainedmonkey.com/projects/colobus/">colobus</a>.
   </p>
  </div>
  <table class="standard">
   <tr>
    <th>name</th>
    <th>messages</th>
    <th>rss</th>
    <th>rdf</th>
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
    echo "        <td class=\"align-right\">", $details['high'] - $details['low'] + 1, "</td>\n";
    echo "        <td>";
    if ($details['status'] != 'n') {
        echo "<a href=\"group.php?group=$group&amp;format=rss\">rss</a>";
    }
    echo "</td>\n";
    echo "        <td>";
    if ($details['status'] != 'n') {
        echo "<a href=\"group.php?group=$group&amp;format=rdf\">rdf</a>";
    }
    echo "</td>\n";
    echo "       </tr>\n";
}
?>
  </table>
 </section>
<?php

foot();
