<?php
/*
  +----------------------------------------------------------------------+
  | PHP.net Website Systems                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2011 The PHP Group                                     |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  |	Kalle Sommer Nielsen <kalle@php.net>                             |
  +----------------------------------------------------------------------+
*/

class Preferences {
    protected $data      = Array(
                                 'language'             => Array('en', 'en'), 
                                 'miniflags'            => Array(false, false), 
                                 'popular_groups'	=> Array(2, 2), 
                                 'email_address'	=> Array('', '') 
			         );

    public function __construct() {
        foreach($this->data as $key => $default) {
            if (!isset($_COOKIE['pref_' . $key])) {
                continue;
            }

            $tmp = $_COOKIE['pref_' . $key];
            settype($tmp, gettype($default[1]));

            $this->data[$key][1] = $tmp;
        }
    }

    public function __get($var) {
        if (isset($this->data[$var])) {
            return $this->data[$var][1];
        }

        return false;
    }

    public function save() {
        $time = (isset($_SERVER['REQUEST_TIME']) ? (integer) $_SERVER['REQUEST_TIME'] : time());

        foreach($this->data as $key => $default) {
            if (!isset($_POST['pref_' . $key])) {
                continue;
            }

            $new_value = $this->data[$key][0];

            if ($this->validate($key, $_POST['pref_' . $key])) {
                $new_value = $this->data[$key][1] = $_COOKIE['pref_' . $key] = $_POST['pref_' . $key];

            }

            setcookie('pref_' . $key, $new_value, $time + 31449600, SERVER_PATH, SERVER_NAME, (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'), true);
        }
    }

    public function validate($key, &$data) {
        switch ($key) {
            case 'email_address':
                return filter_var($data, FILTER_VALIDATE_EMAIL);
            case 'miniflags':
                return true;
            case 'popular_groups':
                return $data > -1 || $data < 3;
            case 'language':
                global $langs;

                return in_array($data, $langs);
        }

        return false;
    }
}

?>