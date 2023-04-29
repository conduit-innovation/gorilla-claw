<?php

namespace MonkeyHook;

class Hook implements HookInterface
{
    public $function_key;
    public $callback;
    public $hook_name;
    public $priority;
    public $that;

    function __construct($hook_name, $callback, $priority, $function_key)
    {
        $this->function_key = $function_key;
        $this->callback = $callback;
        $this->hook_name = $hook_name;
        $this->priority = $priority;
        $this->that = null;
        
        if (is_array($callback['function'])) {
            if (is_object($callback['function'][0])) { 
                $this->that = &$callback['function'][0];
            } else {
                $this->that = $callback['function'][0];
            }
        }
    }

    public function remove(): bool
    {
        return remove_filter($this->hook_name, $this->callback, $this->priority);
    }

    public function replace(callable $callback): bool
    {
        global $wp_filter;
        if (
            isset($wp_filter[$this->hook_name]) &&
            isset($wp_filter[$this->hook_name]->callbacks[$this->priority]) &&
            isset($wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key])
        ) {

            if (!is_null($this->that) && is_object($this->that)) {
                // $this rebinding
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] = (new HookProxy($callback, $this->that))->__cb;
                $this->callback['function'] = [&$this->that, $callback];
            } elseif (!is_null($this->that) && is_string($this->that)) {
                // Static binding
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] = $callback;
                $this->callback['function'] =  $callback;
            } elseif (is_null($this->that)) {
                // No rescoping or binding
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] = $callback;
                $this->callback['function'] = $callback;
            } else {
                echo('bad hook');
                // Bad hook format, just return false
                return false;
            }
            return true;
        }

        return false;
    }

    public function exists(): bool
    {
        return true;
    }
}
