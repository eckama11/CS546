<?php

class Base {

    public function __get($prop) {
        $getter = Array($this, 'get'. strtoupper(substr($prop, 0, 1)) . substr($prop, 1));
        if (!is_callable($getter)) {
            throw new Exception(__CLASS__ .": No such property '$prop'");
        }
        return call_user_function(Array($this, $getter));
    } // __get

    public function __set($prop, $value) {
        $getter = Array($this, 'set'. strtoupper(substr($prop, 0, 1)) . substr($prop, 1));
        if (!is_callable($setter)) {
            throw new Exception(__CLASS__ .": No such property '$prop'");
        }
        return call_user_function(Array($this, $setter), $value);
    } // _set

} // class Base
