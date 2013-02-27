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

class Template implements ArrayAccess {
    protected $name;
    protected $vars = Array();

    public function __construct($template) {
        global $prefs, $lang;

        $this->name          = $template;
        $this->vars['prefs'] = $prefs;
        $this->vars['lang']  = $lang;
    }

    public function __toString() {
        return (string) $this->parse();
    }

    public function parse() {
        ob_start();
        extract($this->vars, EXTR_SKIP);
        include 'templates/' . $this->name . '.tpl';

        return ob_get_clean();
    }

    public function offsetGet($offset) {
        if (isset($this->vars[$offset])) {
            return $this->vars[$offset];
        }
    }

    public function offsetSet($offset, $value) {
        $this->vars[$offset] = $value;
    }

    public function offsetUnset($offset) {
        if (isset($this->vars[$offset])) {
            unset($this->vars[$offset]);
        }
    }

    public function offsetExists($offset) {
        return isset($this->vars[$offset]);
    }
}

?>