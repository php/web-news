<?php
require 'common.inc.php';

$q = isset($_GET['q']) ? $_GET['q'] : false;

if ($q && get_magic_quotes_gpc()) $q = stripslashes($q);

head("news search");
?>
<blockquote>
sorry, search is offline for now.
</blockquote>
<?php

foot();
exit;

if ($q) {
  $udm = udm_alloc_agent("mysql://root@localhost/news_search/", "crc-multi");
  if ($p) udm_set_agent_param($udm, UDM_PARAM_PAGE_NUM, $p);
  if ($t) udm_add_search_limit($udm, UDM_LIMIT_TAG, substr($t,0,11));
  udm_set_agent_param($udm, UDM_PARAM_PAGE_SIZE, 20);
  udm_set_agent_param($udm, UDM_PARAM_TRACK_MODE, UDM_TRACK_DISABLED);
  $res = udm_find($udm,$q)
    or die("search failed"); ### XXX
  $found = udm_get_res_param($res, UDM_PARAM_FOUND);
  if ($found) {
    $first = udm_get_res_param($res, UDM_PARAM_FIRST_DOC);
    $last = udm_get_res_param($res, UDM_PARAM_LAST_DOC);
    $wordinfo = udm_get_res_param($res, UDM_PARAM_WORDINFO);
    $time = udm_get_res_param($res, UDM_PARAM_SEARCHTIME);
    navbar($q,$first,$last,$found,$wordinfo,$time);
    echo '<table class="alist" width="100%">';
    echo '<tr>'.
	 '<td class="alisthead">#</td>'.
	 '<td class="alisthead">subject</td>'.
	 '<td class="alisthead">from</td>'.
	 '<td class="alisthead">date</td>'.
	 '</tr>';
    $c = udm_get_res_param($res, UDM_PARAM_NUM_ROWS);
    for ($i = 0; $i < $c; $i++) {
      $class = $i % 2 ? "odd" : "even";
      $u = udm_get_res_field($res,$i,UDM_FIELD_URL);
      $t = udm_get_res_field($res,$i,UDM_FIELD_TITLE);
      $b = udm_get_res_field($res,$i,UDM_FIELD_TEXT);
      $m = udm_get_res_field($res,$i,UDM_FIELD_MODIFIED);
      $f = udm_get_res_field($res,$i,UDM_FIELD_KEYWORDS);
      $u = preg_replace("#^file:/home/news/articles/(.+?)/(\\d+)\$#e","gen_url('\\1','\\2')", $u);
      echo "<tr class=\"$class\"><td>";
      echo "<a href=\"$u\">".($i+$first)."</a>";
      echo "</td><td>";
      echo format_subject($t);
      echo "</td><td>";
      echo format_author($f);
      echo "</td><td>";
      # yeah, this is a pretty dumb round-trip on formatting the time. whatever.
      echo $m > 0 ? format_date(strftime("%c", $m)) : "&nbsp;";
      echo "</td></tr>\n";
      echo "<tr class=\"$class\"><td></td><td colspan=\"3\">".nl2br(htmlspecialchars($b, ENT_QUOTES, "UTF-8"))."</td></tr>\n";
    }
    echo "</table>";
    navbar($q,$first,$last,$found,$wordinfo,$time);
    echo '<a href="http://www.mnogosearch.ru/"><img align="left" src="i/udm.gif" border="0" width="102" height="25" alt="Powered by mnoGoSearch"></a>';
    foot();
    exit();
  }
}

function gen_url($group,$article) {
  return "article.php?group=".ereg_replace("/",".",$group)."&amp;article=$article";
}

# display search box, possibly with message about no results
if ($q) {
  echo "<p><b>No results found!</b></p>";
}

echo "<p>Use the box up top for now. More features later.</p>";

foot();

function navbar($q,$first,$last,$found,$wordinfo,$time) {
  echo '<table border="0" cellpadding="2" cellspacing="2" width="100%"><tr class="alisthead">';
  echo '<td width="20%">';
  $p = floor($first / 20);
  if ($p > 0) {
    echo "<a href=\"search.php?q=".htmlspecialchars(urlencode($q), ENT_QUOTES, "UTF-8")."&amp;p=".($p-1).($GLOBALS["t"]?"&amp;t=".$GLOBALS["t"]:"")."\"><b>&laquo; previous</b></a>";
  }
  else {
    echo "&nbsp;";
  }
  echo '</td>';
  $j = min($i+20,$l);
  $c = $l - $f + 1;
  echo '<td align="center" class="alisthead" width="60%">'.htmlspecialchars("found: $wordinfo in $time secs", ENT_QUOTES, "UTF-8")." ($first-$last of $found)</td>";
  echo '<td align="right" width="20%">';
  $maxpages = floor($found / 20);
  if ($p < $maxpages) {
    echo "<a href=\"search.php?q=".htmlspecialchars(urlencode($q), ENT_QUOTES, "UTF-8")."&amp;p=".($p+1).($GLOBALS["t"]?"&amp;t=".$GLOBALS["t"]:"")."\"><b>next &raquo;</b></a>";
  }
  else {
    echo "&nbsp;";
  }
  echo '</td>';
  echo '</tr></table>';
}


