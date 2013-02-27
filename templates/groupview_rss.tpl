<?php echo '<?xml version="1.0" encoding="utf-8"?>' . "\r\n"; ?>
<rss version="2.0">
 <channel> 
  <title><?php echo NNTP_SERVER ?>: <?php echo $group; ?></title>
  <link>http://<?php echo SERVER_NAME . SERVER_PATH . $group; ?>/</link>
  <description><?php echo $group; ?> <?php echo $lang['groups_feeds_newsgroup_at']; ?> <?php echo NNTP_SERVER; ?></description>
  <lastBuildDate><?php echo $date; ?></lastBuildDate>
  <pubDate><?php echo $date; ?></pubDate>

  <?php echo $groups; ?>
 </channel>
</rss>