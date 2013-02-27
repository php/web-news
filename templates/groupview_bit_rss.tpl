<item>
 <title><?php echo $subject = format_subject($subject); ?></title>
 <description><?php echo $subject; ?> <?php echo $lang['by']; ?> <?php echo str_replace('&nbsp;', ' ', strip_tags(format_author($author))); ?></description>
 <link>http://<?php echo SERVER_NAME . SERVER_PATH . $group; ?>/<?php echo $id; ?>/</link>
 <guid>http://<?php echo SERVER_NAME . SERVER_PATH . $group; ?>/<?php echo $id; ?>/</guid>
 <pubDate><?php echo format_xml_date($date); ?></pubDate>
</item>
