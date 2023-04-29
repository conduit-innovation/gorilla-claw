<?php namespace MonkeyHook;


class HookProxyStatic extends HookProxy {
    function __construct(&$hook_cb, &$that) {
        $proxy = &$this;
        $this->__that = &$that;
        $this->__cb = \Closure::bind($hook_cb, null, $that);
    }
 
    function __get($prop) {
    }
 
    function __set($prop, $val) {
    }
 
    function __call($method, $args) {
    }
     
    public function &__get_private($var): mixed {
    }
}