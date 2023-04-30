<?php namespace MonkeyHook;

interface HookInterface {
    public function remove(): bool;
    public function replace(callable $cb): bool;
    public function exists(): bool;
    public function rebind(string $hook_name, callable $callback, int $priority = 10, $accepted_args = 1);
 }
 