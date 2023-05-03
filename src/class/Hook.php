<?php

namespace GorillaClaw;

class Hook
{
    public $function_key;
    public $callback;
    public $hook_name;
    public $priority;
    public $that;
    public $original_callback;
    
    /**
     * __construct
     *
     * @param  mixed $hook_name
     * @param  mixed $callback
     * @param  mixed $priority
     * @param  mixed $function_key
     * @return void
     */
    
    function __construct($hook_name, $callback, $priority, $function_key)
    {
        global $wp_filter;

        $this->function_key = $function_key;
        $this->callback = $callback;
        $this->hook_name = $hook_name;
        $this->priority = $priority;
        $this->that = null;
        $this->original_callback = isset($wp_filter[$this->hook_name]) ? $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] : false;

        if (is_array($callback['function'])) {
            if (is_object($callback['function'][0])) { 
                $this->that = &$callback['function'][0];
            } else {
                $this->that = $callback['function'][0];
            }
        }
    }
    
    /**
     * Remove and un-hook the handler
     *
     * @return bool `true` on success
     */
    
    public function remove(): bool
    {
        return remove_filter($this->hook_name, $this->callback['function'], $this->priority);
    }
    
    /**
     * replace
     *
     * @param  mixed $callback
     * @return bool 
     */
    
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
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] = (new HookProxy($callback, $this->that))->___cb;
                $this->callback['function'] = [&$this->that, $callback];
            } elseif (!is_null($this->that) && is_string($this->that)) {
                // Static binding
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] = $callback;
                $this->callback['function'] =  $callback;
            } elseif (is_null($this->that)) {
                // No rescoping or binding
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'] = $callback;
                $this->callback['function'] = $callback;
            } 
            
            return true;
        }

        return false;
    }
        
    /**
     * rebind
     *
     * @param  mixed $hook_name
     * @param  mixed $callback
     * @param  mixed $priority
     * @param  mixed $accepted_args
     * @return void
     */

    public function rebind(string $hook_name, callable $callback, int $priority = 10, $accepted_args = 1) {
        if(is_null($this->that)) {
            throw new \ErrorException('Cannot rebind from a non-object hook');
        }

        add_filter($hook_name, (new HookProxy($callback, $this->that))->___cb, $priority, $accepted_args);
    }

    public function inject($before, $after = false) {
        $original_callback = &$this->original_callback;

        $this->replace(function(...$args) use ($before, $after, $original_callback) {
            if($before) {
                if(_is_callable_object($original_callback)) {
                    $args[0] = (new HookProxy($before, $original_callback[0]))->___cb(...$args); 
                } else {
                    $args[0] = $before(...$args);
                }
            }
                    
            $args[0] = call_user_func_array($original_callback, $args);
            
            if($after) {
                if(_is_callable_object($original_callback)) {
                    $args[0] = (new HookProxy($after, $original_callback[0]))->___cb(...$args); 
                } else {
                    $args[0] = $after(...$args);
                }
            }

            return $args[0];
        });
    }

    public function intercept($on_get = false, $on_call = false, $on_set = false): bool
    {
        global $wp_filter;
        if (
            isset($wp_filter[$this->hook_name]) &&
            isset($wp_filter[$this->hook_name]->callbacks[$this->priority]) &&
            isset($wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key])
        ) {

            if (!is_null($this->that) && is_object($this->that)) {
                // $this rebinding
                $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'][0] = (new ObjectInterceptor($wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'][0], $on_get, $on_call, $on_set));
                
                $this->callback['function'][0] = $wp_filter[$this->hook_name]->callbacks[$this->priority][$this->function_key]['function'][0];
                return true;
            } 
            
            
        }

        return false;
    }
}
