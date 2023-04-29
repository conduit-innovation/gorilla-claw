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

        foreach($haystack as $function_key => $callback_definition) {

            if(is_array($needle)) {
                // We are looking for a static or object callback
                
                if(!is_array($callback_definition['function']))
                    continue;

                $obj = $callback_definition['function'][0];
                $method = $callback_definition['function'][1];

                if(get_class($obj) === $needle[0]) {
                    if($method === $needle[1]) {
                        $ret[$function_key] = $callback_definition;
                    }
                }

            } else {
                if($callback_definition['function'] === $needle) {
                    $ret[$function_key] = $callback_definition;
                }
            }
        }

        return $ret;
    }

    private function add_to_wp_filter_structure(&$wp_filter, $hook_name, $callbacks, $priority) {
        if(!isset($wp_filter[$hook_name])) {
            $wp_filter[$hook_name] = [$priority => []];
        }

        if(!isset($wp_filter[$hook_name][$priority])) {
            $wp_filter[$hook_name][$priority] = [];
        }

        $wp_filter[$hook_name][$priority] += is_array($callbacks) ? $callbacks : [$callbacks];
    } 
}