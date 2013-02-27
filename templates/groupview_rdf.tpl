<?php echo '<?xml version="1.0" encoding="utf-8"?>' . "\r\n"; ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns" xmlns="http://my.netscape.com/rdf/simple/0.9/">
 <channel>
  <title><?php echo NNTP_SERVER; ?>: <?php echo $group; ?></title>
  <link>http://<?php echo SERVER_NAME . SERVER_PATH . $group; ?>/</link>
  <description><?php echo $group; ?> <?php echo $lang['groups_feeds_newsgroup_at']; ?> <?php echo NNTP_SERVER; ?></description>
  <language>en-US</language>
 </channel>

 <?php echo $groups; ?>
</rdf:RDF>