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
        if(isset($this->$prop)) {
            $this->__that->$prop = $val;
        } else {
            $private = &$this->__get_private($prop);
            $private = $val;
        }
    }
 
    function __call($method, $args) {

        if($method === '__cb') {
            return $this->__cb->bindTo($this->__that, $this->__that)(...$args);
        }

        try {
            return $this->__that->$method(...$args);
        } catch (\Exception  $e) {
            $private = &$this->__get_private($method);
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