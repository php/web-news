<?php

require 'common.php';

$s = nntp_connect(NNTP_HOST);
if (!$s) {
	error("Failed to connect to news server");
}

if (!nntp_cmd($s,"LIST",215)) {
	error("failed to get list of news groups");
}

head();

?>
  <table border="0" cellpadding="6" cellspacing="0">
   <tr>
     <td>
      <table class="grouplist">
       <tr class="alisthead">
        <td>name</td>
        <td>messages</td>
        <td>rss</td>
        <td>rdf</td>
       </tr>
<?php
$class = "even";
while ($line = fgets($s, 1024)) {
	if ($line == ".\r\n") {
		break;
	}
	$line = chop($line);
	list($group, $high, $low, $active) = explode(" ", $line);
	echo "       <tr>\n";
	echo "        <td class=\"$class\"><a class=\"active$active\" href=\"/$group\">$group</a></td>\n";
	echo "        <td align=\"right\" class=\"$class\">", $high-$low+1, "</td>\n";
	echo "        <td class=\"$class\">";
	if ($active != 'n') {
		echo "<a href=\"group.php?group=$group&amp;format=rss\">rss</a>";
	}
	echo "</td>\n";
	echo "        <td class=\"$class\">";
	if ($active != 'n') {
		echo "<a href=\"group.php?group=$group&amp;format=rdf\">rdf</a>";
	}
	echo "</td>\n";
	echo "       </tr>\n";
	$class = ($class == "even") ? "odd" : "even";
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
