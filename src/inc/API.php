<?php

namespace MonkeyHook;

function find_action(string | array $hook_name, mixed $callback = false) {
    return find_hooks($hook_name, $callback);
}

function find_filter(string | array $hook_name, mixed $callback = false) {
    return find_hooks($hook_name, $callback);
}

function find_hooks(string | array $hook_name, mixed $callback = false) {
    global $wp_filter;
    return (new Query($wp_filter))->find($hook_name, $callback);
}

function locate_hook_by_classname(string $hook_name, string $class_name, string | bool $method = false): Hook {
   global $wp_filter;

   if(isset($wp_filter[$hook_name])) {
       foreach($wp_filter[$hook_name]->callbacks as $priority => $hooks) {
           foreach($hooks as $function_key => $callable) {
               if(!is_array($callable['function'])) {
                   continue;
               }
               $obj = $callable['function'][0];
               $meth = $callable['function'][1];

               if(get_class($obj) === $class_name) {
                   if($meth === $method || $method === false) {
                       return new Hook($hook_name, $callable['function'], $priority, $function_key, $obj);
                   }
               }
           }
       }
   }

   return new HookNotFound();
}