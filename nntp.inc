<?php

function nntp_connect($server, $port = 119) {
	$s = @fsockopen($server, $port, $errno, $errstr, 30);
	if (!$s) {
		return false;
	}
	$hello = fgets($s, 1024);
	if (substr($hello,0,4) != "200 ") {
		return false;
	}
	return $s;
}

function nntp_cmd($conn, $command, $expected) {
	if (strlen($command) > 510) {
		die("command too long: $command");
	}
	fputs($conn, "$command\r\n");
	$res = fgets($conn, 1024);
	list($code, $extra) = explode(" ", $res, 2);
	return ($code == $expected) ? $extra : false;
}
