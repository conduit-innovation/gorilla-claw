<?php namespace MonkeyHook;

class HookNotFound extends Hook {
   public function __construct() {}

   public function remove(): bool {
       return false;
   }

   public function replace(callable $cb): bool {
       return false;
   }

   public function exists(): bool {
       return false;
   }
}
