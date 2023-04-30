<?php namespace MonkeyHook;

class HookProxy {
   
    public $__cb;
    protected $__that;
 
    function __construct(&$hook_cb, &$that) {
        $proxy = &$this;
        $this->__that = &$that;
        $this->__cb = \Closure::bind(function (...$args) use (&$proxy, &$hook_cb) {
            return \Closure::bind($hook_cb, $proxy)(...$args);
        }, $that);
    }
 
    function __get($prop) {
        return $this->__get_private($prop);
    }
 
    function __set($prop, $val) {
        $private = &$this->__get_private($prop);
        $private = $val;
    }
 
    function __call($method, $args) {

        if($method === '__cb') {
            return $this->__cb->bindTo($this->__that, $this->__that)(...$args);
        }

        return $this->__call_private($method, $args);
    }
     
    public function &__get_private($var): mixed {
        $that = &$this->__that;
        return \Closure::bind(function &($that) use ($var) {
            return $that->$var;
        }, $that, $that)($that);
    }

    public function __call_private($var, $args): mixed {
        $that = &$this->__that;
        return \Closure::bind(function ($that) use ($var, $args) {
            return $that->$var(...$args);
        }, $that, $that)($that);
    }
 }