<?php namespace GorillaClaw;

class ObjectInterceptor extends HookProxy {

    private $___evts;
    private $___proxy;

    function __construct(&$that, $on_get = false, $on_call = false, $on_set = false) {
        
        $this->___proxy = &$that; // Keep a reference alive to object, to stop GC eating it up
        $this->___that = $that;

        $this->___evts = [];
        
        if($on_get) {
            $this->___on('get', $on_get);
        }

        if($on_call) {
            $this->___on('call', $on_call);
        }

        if($on_set) {
            $this->___on('set', $on_set);
        }
    }

    function __get($prop) {

        // if(str_starts_with($prop, '__')) {
        //     return $this->$prop;
        // }

        $this->___trigger('get', $prop);
        return parent::__get($prop);
    }
 
    function __set($prop, $val) {
        $this->___trigger('set', $prop, $val);
        parent::__set($prop, $val);
    }

    private function ___on($evt, $callback) {
        $this->___evts[$evt] = $callback;
    }

    private function ___trigger($evt, $prop, $args = null) {
        if(isset($this->___evts[$evt])) 
            return $this->___evts[$evt]($prop, $args);
        
        return null;
    }
 
    function __call($method, $args) {
        $rc = $this->___trigger('call', $method, $args);

        if(is_null($rc)) {
            return parent::__call($method, $args);
        } else {
            return $rc;
        }
    }
}