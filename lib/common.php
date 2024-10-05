<?php

/* Prevents the poor mail server from suffering if it receives a message with many references */
/* (References: <xxx> or In-Reply-To: <xxx>) */
define('REFERENCES_LIMIT', 20);

function error($str)
{
    head("PHP news : error");
    echo "<section class=\"content\"><blockquote><strong>Error:</strong> ",
       to_utf8($str), "</blockquote></section>\n";
    foot();
    die();
}

/* Borrowed from web-php repo. */
function clean($var)
{
    return htmlspecialchars($var, \ENT_QUOTES);
}

// Try to check that this email address is valid
function is_emailable_address($email)
{
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    // No email, no validation
    if (!$email) {
        return false;
    }

    $host = substr($email, strrpos($email, '@') + 1);
    // addresses from our mailing-list servers
    $host_regex = "!(lists\.php\.net|chek[^.*]\.com)!i";
    if (preg_match($host_regex, $host)) {
        return false;
    }

    return true;
}

// Returns the real IP address of the user
function i2c_realip()
{
    // No IP found (will be overwritten by for
    // if any IP is found behind a firewall)
    $ip = false;

    // If HTTP_CLIENT_IP is set, then give it priority
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }

    // User is behind a proxy and check that we discard RFC1918 IP addresses
    // if they are behind a proxy then only figure out which IP belongs to the
    // user.  Might not need any more hackin if there is a squid reverse proxy
    // infront of apache.
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Put the IP's into an array which we shall work with shortly.
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = false;
        }

        for ($i = 0; $i < count($ips); $i++) {
            // Skip RFC 1918 IP's 10.0.0.0/8, 172.16.0.0/12 and
            // 192.168.0.0/16
            // Also skip RFC 6598 IP's
            $regex = '/^(?:10|100\.(?:6[4-9]|[7-9]\d|1[01]\d|12[0-7])|172\.(?:1[6-9]|2\d|3[01])|192\.168)\./';
            if (!preg_match($regex, $ips[$i]) && ip2long($ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }

    // Return with the found IP or the remote address
    return $ip ?: $_SERVER['REMOTE_ADDR'];
}

/*
 This code is used to post data to the central server which
 can store the data in database and/or mail notices or requests
 to PHP.net stuff and servers
*/
function posttohost($url, $data)
{
    $data = http_build_query($data);

    $opts = [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $data,
    ];

    $ctx = stream_context_create(['http' => $opts]);

    return file_get_contents($url, false, $ctx);
}

function head($TITLE = "PHP Mailing Lists (PHP News)")
{
    $SUBDOMAIN = 'lists';
    $LINKS = [
        [ 'href' => '//php.net/downloads.php',    'text' => 'Downloads' ],
        [ 'href' => '//php.net/get-involved.php', 'text' => 'Get Involved' ],
        [ 'href' => '//php.net/docs.php',         'text' => 'Documentation' ],
        [ 'href' => '//php.net/support.php',      'text' => 'Help' ],
    ];
    $CURRENT_PAGE = 'Help';
    $CSS = [ ]; // $CSS is relative to shared for some godforsaken reason
    $HEAD_WIKI = '<link rel="stylesheet" href="/style.css" type="text/css" />';
    $SEARCH = [
                   "method"      => "get",
                   "action"      => "//php.net/search.php",
                   "placeholder" => "Search",
                   "name"        => "pattern",
    ];
    include __DIR__ . '/../shared/templates/header.inc';
}

function foot()
{
    include __DIR__ . '/../shared/templates/footer.inc';
}

function to_utf8($str, $charset = 'iso-8859-1')
{
    $n = iconv($charset, 'utf-8', $str);
    if ($n === false) {
        return $str;
    }
    return $n;
}

function decode_header($charset, $encoding, $text)
{
    if (strtolower($encoding) == "b") {
        $text = base64_decode($text);
    } else {
        $text = str_replace('_', ' ', quoted_printable_decode($text));
    }
    return to_utf8($text, $charset);
}

function recode_header($header, $basecharset)
{
    if (strpos($header, "=?") === false) {
        return to_utf8($header, $basecharset);
    }
    return preg_replace_callback(
        "/=\\?(.+?)\\?([qb])\\?(.+?)(\\?=|$)/i",
        function ($m) {
            return decode_header($m[1], $m[2], $m[3]);
        },
        $header
    );
}

/* Email spam protection (taken from php-bugs-web) */
function spam_protect($txt)
{
    $translate = array('@' => ' at ', '.' => ' dot ');

    /* php.net addresses are not protected! */
    if (preg_match('/^(.+)@php\.net/i', $txt)) {
        return $txt;
    } else {
        return strtr($txt, $translate);
    }
}


# this turns some common forms of email addresses into mailto: links
function format_author($a, $charset = 'iso-8859-1')
{
    $a = recode_header($a, $charset);
    if (preg_match("/^\s*(.+)\s+\\(\"?(.+?)\"?\\)\s*$/", $a, $ar)) {
        return "<a href=\"mailto:" .
            htmlspecialchars(urlencode(spam_protect($ar[1])), ENT_QUOTES, "UTF-8") .
            "\" class=\"email fn n\">" .
            str_replace(" ", "&nbsp;", htmlspecialchars($ar[2], ENT_QUOTES, "UTF-8")) . "</a>";
    }
    if (preg_match("/^\s*\"?(.+?)\"?\s*<(.+)>\s*$/", $a, $ar)) {
        return "<a href=\"mailto:" .
            htmlspecialchars(urlencode(spam_protect($ar[2])), ENT_QUOTES, "UTF-8") .
            "\" class=\"email fn n\">" .
            str_replace(" ", "&nbsp;", htmlspecialchars($ar[1], ENT_QUOTES, "UTF-8")) . "</a>";
    }
    if (strpos("@", $a) !== false) {
        $a = spam_protect($a);
        return "<a href=\"mailto:" . htmlspecialchars(urlencode($a), ENT_QUOTES, "UTF-8") .
            "\" class=\"email fn n\">" . htmlspecialchars($a, ENT_QUOTES, "UTF-8") . "</a>";
    }
    return str_replace(" ", "&nbsp;", htmlspecialchars($a, ENT_QUOTES, "UTF-8"));
}

function format_subject($s, $charset = 'iso-8859-1')
{
    global $article;
    $s = recode_header($s, $charset);

    /* Trim most of the prefixes we add for lists */
    $s = preg_replace('/^(Re:\s*)?(\s*\[(DOC|PEAR|PECL|PHP|ANNOUNCE|GIT-PULLS|STANDARDS|php-standards)(-.+?)?]\s*)+/', '\1', $s);

    // make this look better on the preview page..
    if (strlen($s) > 150 && !isset($article)) {
        $s = substr($s, 0, 150) . "...";
    } else {
        $s = wordwrap($s, 150);
    }
    return nl2br(htmlspecialchars($s, ENT_QUOTES, "UTF-8"));
}


function format_title($s, $charset = 'iso-8859-1')
{
    global $article;
    $s = recode_header($s, $charset);
    $s = preg_replace("/^(Re: *)?\[(PHP|PEAR)(-.*?)?\] /i", "\\1", $s);
    // make this look better on the preview page..
    if (strlen($s) > 150 && !isset($article)) {
        $s = substr($s, 0, 150) . "...";
    } else {
        $s = wordwrap($s, 150);
    }
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function format_date($d)
{
    $d = strtotime($d);
    $d = gmdate('r', $d);
    return str_replace(" ", "&nbsp;", $d);
}

/*
 * Translate a group name to the email address for the list. It's almost
 * easy but then we have a bunch of special cases.
 */
function get_list_address($group)
{
    $address = str_replace('.', '-', $group); // php.internals -> php-internals
    $address = str_replace('php-doc-', 'doc-', $address); // php-doc-fr -> doc-fr
    $address = str_replace('php-pear-', 'pear-', $address); // php-pear-dev -> pear-dev
    $address = str_replace('php-pecl-', 'pecl-', $address); // php-pecl-dev -> pecl-dev
    $address = str_replace('php-standards', 'standards-', $address); // php-standards-cvs -> standards-cvs

    $special = [
        'doc-chm' => 'php-doc-chm', # revert earlier removal of php-
        'php-internals' => 'internals',
        'php-internals-win' => 'internals-win',
        'php-doc' => 'phpdoc',
        'php-general-bg' => 'general-bg',
        'php-general-es' => 'general-es',
        'php-git-pulls' => 'git-pulls',
        'php-pres' => 'pres',
        'php-pdo' => 'pdo',
    ];

    if (array_key_exists($address, $special)) {
        $address = $special[$address];
    }

    return $address;
}

function get_subscribe_address($group, $mode = '')
{
    return get_list_address($group) . '+subscribe' . ($mode ? '-' . $mode : '') . '@lists.php.net';
}
