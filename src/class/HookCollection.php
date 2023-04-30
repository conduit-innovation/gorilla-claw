<?php namespace MonkeyHook;

class HookCollection implements \ArrayAccess, \Countable, \Iterator {
    protected array $wp_filter;
    protected array $callbacks;

    function __construct(array $wp_filter) {
        $this->wp_filter = $wp_filter;

        $collect = [];

        array_walk($this->wp_filter, function($entry, $hook_name) use (&$collect) {
            foreach($entry as $priority => $callbacks) {
                foreach($callbacks as $function_key => $callback) {
                    $collect[] = new Hook($hook_name, $callback, $priority, $function_key);
                }
            }
        });
        
        $this->callbacks = $collect;
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->callbacks[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->callbacks[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        $this->callbacks[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->callbacks[$offset]);
    }

    function __get($prop) {
        $ret = [];

        foreach($this->callbacks as $hook) {
            $ret[$hook->function_key] = $hook->$prop;
        }

        return $ret;
    }

    function __call($prop, $args) {
        $prohibit = ['rebind'];
        $ret = [];
        
        if(in_array($prop, $prohibit)) {
            throw new \ErrorException("Cannot rebind from a HookCollection");
        }

        foreach($this->callbacks as $hook) {
            $ret[$hook->function_key] = $hook->$prop(...$args);
        }

        return $ret;
    }

    public function count(): int {
        return count($this->callbacks);
    }

    public function rewind(): void {
        rewind($this->callbacks);
    }

    public function next(): void {
        next($this->callbacks);
    }

    public function current(): Hook {
        return current($this->callbacks);
    }

    public function valid(): bool {
        return key($this->callbacks) !== null;
    }

    public function key(): int {
        return key($this->callbacks);
    }
}