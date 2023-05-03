<?php namespace GorillaClaw;

class HookProxy {
   
    public $___cb;
    protected $___that;
 
    function __construct(&$hook_cb, &$that) {
        $proxy = &$this;
        $this->___that = &$that;
        $this->___cb = \Closure::bind(function (...$args) use (&$proxy, &$hook_cb) {
            return \Closure::bind($hook_cb, $proxy)(...$args);
        }, $that);
    }
 
    function __get($prop) {
        return $this->___get_private($prop);
    }
 
    function __set($prop, $val) {
        $private = &$this->___get_private($prop);
        $private = $val;
    }
 
    function __call($method, $args) {

        if($method === '___cb') {
            return $this->___cb->bindTo($this->___that, $this->___that)(...$args);
        }

        return $this->___call_private($method, $args);
    }
     
    public function &___get_private($var): mixed {
        $that = &$this->___that;
        return \Closure::bind(function &($that) use ($var) {
            return $that->$var;
        }, $that, $that)($that);
    }

    public function ___call_private($var, $args): mixed {
        $that = &$this->___that;
        return \Closure::bind(function ($that) use ($var, $args) {
            return $that->$var(...$args);
        }, $that, $that)($that);
    }
 }