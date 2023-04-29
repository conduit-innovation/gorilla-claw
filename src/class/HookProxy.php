<?php namespace MonkeyHook;


class HookProxy {
   
    public $__cb;
    protected $__that;
 
    function __construct(&$hook_cb, &$that) {
        $proxy = &$this;
        $this->__that = &$that;
        $this->__cb = \Closure::bind(function (...$args) use (&$proxy, &$hook_cb) {
            \Closure::bind($hook_cb, $proxy)(...$args);
        }, $that);
    }
 
    function __get($prop) {
        try {
            return $this->$prop;
        } catch (\Exception  $e) {
            return $this->__get_private($prop);
        }
    }
 
    function __set($prop, $val) {
        try {
            $this->$prop = $val;
        } catch (\Exception  $e) {
            $private = $this->__get_private($prop);
            $private = $val;
        }
    }
 
    function __call($method, $args) {
        try {
            return $this->$method(...$args);
        } catch (\Exception  $e) {
            $private = $this->__get_private($method);
            return \Closure::bind($private, $this->__that, $this->__that)(...$args);
        }
    }
     
    public function &__get_private($var): mixed {
        $that = &$this->__that;
        return \Closure::bind(function &($that) use ($var) {
            return $that->$var;
        }, $that, $that)($that);
    }
 }