<?php namespace MonkeyHook;

interface HookInterface {
    public function remove(): bool;
    public function replace(callable $cb): bool;
    public function exists(): bool;
 }
 