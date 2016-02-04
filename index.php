<?php

require 'common.php';

try {
	$nntpClient = new \Web\News\Nntp(NNTP_HOST);
	$groups = $nntpClient->listGroups();
} catch (Exception $e) {
	error($e->getMessage());
}

head();

?>
  <table border="0" cellpadding="6" cellspacing="0">
   <tr>
     <td>
      <table class="stripped">
       <tr>
        <th>name</th>
        <th>messages</th>
        <th>rss</th>
        <th>rdf</th>
       </tr>
<?php
foreach ($groups as $group => $details) {
	echo "       <tr>\n";
	echo "        <td><a class=\"active{$details['status']}\" href=\"/$group\">$group</a></td>\n";
	echo "        <td align=\"right\">", $details['high']-$details['low']+1, "</td>\n";
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
     </td>
     <td valign="top">
      <h1>Welcome!</h1>
      <p>
       This is a completely experimental interface to the PHP mailing lists as 
       reflected on the <a href="news://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'],ENT_QUOTES,"UTF-8"); ?>/">
       <?php echo htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, "UTF-8"); ?> NNTP server</a>.
      </p>
      <p>
       There may be a little more info in the <a href="README">README</a> file.
      </p>
      <p>
       The news server software that is used is <a
       href="http://trainedmonkey.com/colobus/">colobus</a>.
      </p>
     </td>
    </tr>
   </table>
<?php

foot();
