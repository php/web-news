<?php

function navbar($g, $f, $l, $i)
{
    echo '  <table class="standard">' . "\n";
    echo '   <tr>' . "\n";
    echo '    <th class="nav">';
    if ($i > $f) {
        $p = max($i - 20, $f);
        echo "<a href=\"/" . htmlspecialchars($g, ENT_QUOTES, "UTF-8") . "/start/$p\">",
            "<b>&laquo; <span>previous</span></b></a>";
    } else {
        echo "&nbsp;";
    }
    echo '</th>' . "\n";
    $j = min($i + 20, $l);
    $c = $l - $f + 1;
    echo '    <th class="align-center">' . htmlspecialchars($g, ENT_QUOTES, "UTF-8") . " ($i-$j of $c)</th>\n";
    echo '    <th class="nav align-right">';
    if ($i + 20 <= $l) {
        $n = min($i + 20, $l - 19);
        echo "<a href=\"/", htmlspecialchars($g, ENT_QUOTES, "UTF-8") . "/start/$n\">",
            "<b><span>next</span> &raquo;</b></a>";
    } else {
        echo "&nbsp;";
    }
    echo '</th>' . "\n";
    echo '   </tr>' . "\n";
    echo '  </table>' . "\n";
}
