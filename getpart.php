<?php
// vim:sw=2:ts=2:et
if (!$article) die("no article specified");
if (!$part) die("no part specified");

require 'common.inc';
require 'nntp.inc';

$s = nntp_connect(NNTP_HOST)
  or die("failed to connect to news server");

if ($group) {
  $res = nntp_cmd($s,"GROUP $group",211)
    or die("failed to select group .".htmlspecialchars($group));
}
$res = nntp_cmd($s, "ARTICLE $article",220)
  or die("failed to get article ".htmlspecialchars($article));

$emit = false;

$inheaders = 1; $headers = array();
$masterheaders = null;
$mimetype = $boundary = $charset = $encoding = "";
$mimecount = 0; // number of mime parts
$boundaries = array();
$lk = '';
while (!feof($s)) {
  $line = fgets($s, 4096);
  if ($line == ".\r\n") break;
  if ($inheaders && ($line == "\n" || $line == "\r\n")) {
    $inheaders = 0;
    if ($headers['content-type']
        && preg_match("/charset=(\"|'|)(.+)\\1/is", $headers['content-type'], $m)) {
      $charset = trim($m[2]);
    }
    if ($headers['content-type']
        && preg_match("/boundary=(\"|'|)(.+)\\1/is", $headers['content-type'], $m)) {
      $boundaries[] = trim($m[2]);
      $boundary = end($boundaries);
    }
    if ($headers['content-type']
        && preg_match("/([^;]+)(;|\$)/", $headers['content-type'], $m)) {
      $mimetype = trim(strtolower($m[1]));
      ++$mimecount;
    }
    
    $emit = ($mimecount == $part);
	
    $encoding = strtolower(trim($headers['content-transfer-encoding']));
    if ($emit) {
      if ($headers['content-type'])
        header('Content-Type: ' . $headers['content-type']);
      if ($headers['content-disposition'])
        header('Content-Disposition: ' . $headers['content-disposition']);
      if ($headers['content-description'])
        header('Content-Description: ' . $headers['content-description']);
    }
    
    if ($masterheaders == null)
      $headers = $masterheaders;
    continue;
  }
  # fix lines that started with a period and got escaped
  if (substr($line,0,2) == "..") $line = substr($line,1);
  if ($inheaders) {
    list($k,$v) = explode(": ", $line, 2);
    if ($k && $v) {
      $headers[strtolower($k)] = $v;
      $lk = strtolower($k);
    }
    else {
      $headers[$lk] .= $line;
    }
  }
  else {

    if ($boundary
        && substr($line,0,2) == '--'
        && substr($line,2,strlen($boundary)) == $boundary) {

      $inheaders = 1;

      if (substr($line,2+strlen($boundary)) == '--') {
        # end of this container
        array_pop($boundaries);
        $boundary = end($boundaries);
      } else {
        # next section; start with an inherited set of headers
        $headers = $masterheaders;
        $mimetype = "";
      }
      
      continue;
    }

	if (!$emit)
		continue;

    switch($encoding) {
      case "quoted-printable":
        $line = quoted_printable_decode($line);
        break;
      case "base64":
        $line = base64_decode($line);
        break;
    }

    echo $line;
	
  }
}

