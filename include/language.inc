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

class Language implements ArrayAccess {
    protected $languages   = Array();
    protected $translation = Array();

    public function __construct($code) {
        global $langs;

        include 'include/languages/' . $code . '.inc';

        $this->languages   = $langs;
        $this->translation = $translation;

        if ($code != 'en') {
            include 'include/languages/en.inc';

            $this->translation = array_merge($translation, $this->translation);
        }

    }

    public function format() {
        $args    = func_get_args();
        $size    = sizeof($args);
        $args[0] = $this[$args[0]];

        if ($args[0] === NULL || !$size) {
            return '';
        } else if ($size == 1) {
            return $args[0];
        }

        $vars = Array();

        for ($i = 1; $i < $size; ++$i) {
            $vars['{' . ($i) . '}'] = $args[$i];
        }

        return str_replace(array_keys($vars), $vars, $args[0]);
    }

    public function getAvailableLanguages() {
        return $this->languages;
    }

    public function offsetGet($offset) {
        if (isset($this->translation[$offset])) {
            return to_utf8($this->translation[$offset], $this->translation['encoding']);
        }
    }

    public function offsetSet($offset, $value) {
        $this->translation[$offset] = $value;
    }

    public function offsetUnset($offset) {
        if (isset($this->translation[$offset])) {
            unset($this->translation[$offset]);
        }
    }

    public function offsetExists($offset) {
        return isset($this->translation[$offset]);
    }
}

?>