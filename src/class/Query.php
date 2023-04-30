<?php namespace MonkeyHook;

class Query {
    private $wp_filter;

    function __construct(array &$wp_filter) {
        $this->wp_filter = &$wp_filter;
    }

    public function find(string | array $hook_names, string | \Closure | array | false $target_callback = false): HookCollection {
        $results = [];
        
        if(is_string($hook_names)) {
            $hook_names = explode(" ", $hook_names);
        }

        array_walk($this->wp_filter, function($entry, $hook_name) use ($hook_names, $target_callback, &$results) {
            if(in_array($hook_name, $hook_names)) {
                // We have found a matching hook name
  
                foreach($entry->callbacks as $priority => $callbacks) {

                    if($target_callback === false) {
                        $this->add_to_wp_filter_structure($results, $hook_name, $callbacks, $priority);
                        continue;
                    }

                    $found = $this->find_callbacks($target_callback, $callbacks);

                    if(count($found) > 0) {
                        $this->add_to_wp_filter_structure($results, $hook_name, $found, $priority);
                    }

                }
            }
            
            return;
        });

        return new HookCollection($results);
    }

    private function find_callbacks(string | \Closure | array $needle, $haystack): array {

        $ret = [];
        $is_static = false;

        if(is_string($needle) && strpos($needle, "::")) {
            $is_static = true;
        }

        foreach($haystack as $function_key => $callback_definition) {

            if(is_array($needle)) {
                // We are looking for an object only.

                if($callback_definition['function'] instanceof \Closure) {
                    continue;
                }

                $obj = $callback_definition['function'][0];
                $method = $callback_definition['function'][1];

                if(is_string($obj) ? $obj === $needle[0] : get_class($obj) === $needle[0]) {
                    if($method === $needle[1] || $needle[1] === false) {
                        $ret[$function_key] = $callback_definition;
                    }
                } 

            } elseif(is_string($needle) && $is_static) {
                // We are looking for a static method
                
                if(is_string($callback_definition['function'])) {
                    $callback_definition['function'] = explode("::", $callback_definition['function']);
                }

                if($callback_definition['function'] instanceof \Closure) {
                    continue;
                }

                $obj = $callback_definition['function'][0];
                $method = $callback_definition['function'][1];
                list($find_obj, $find_method) = explode("::", $needle);

                if($obj === $find_obj) {
                    if($method === $find_method || empty($find_method)) {
                        $ret[$function_key] = $callback_definition;
                    }
                } 

            } else {
                // We are looking for a function name or a closure

                if($callback_definition['function'] === $needle) {
                    $ret[$function_key] = $callback_definition;
                }
            }

        }

        return $ret;
    }

    public function add_to_wp_filter_structure(array &$wp_filter, string $hook_name, callable | array $callbacks, int $priority) {
        if(!isset($wp_filter[$hook_name])) {
            $wp_filter[$hook_name] = [$priority => []];
        }

        if(!isset($wp_filter[$hook_name][$priority])) {
            $wp_filter[$hook_name][$priority] = [];
        }

        $wp_filter[$hook_name][$priority] += is_array($callbacks) ? $callbacks : [$callbacks];
    } 
}