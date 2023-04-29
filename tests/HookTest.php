<?php

namespace MonkeyHook\Test;

use PHPUnit\Framework\TestCase;
use MonkeyHook\Hook;
use function MonkeyHook\find_hooks;

final class HookTest extends TestCase {
    protected function setUp(): void {
        
    }

    public function testHookConstructor() {
        $hook = new Hook('hook_name', ['function' => [$this, 'some_func']], 10, 'function_key'); 
        $this->assertSame('hook_name', $hook->hook_name);
        $this->assertSame(10, $hook->priority);
        $this->assertSame('function_key', $hook->function_key);
        $this->assertSame($this, $hook->that);

    }

    public function testHookReplace() {
        add_filter('hook_name', [$this, 'some_func'], 10);

        $hook = find_hooks('hook_name');

        $this->assertCount(1, $hook);

        $new_callback = function () {};

        $hook->replace($new_callback);

        $this->assertSame('hook_name', $hook[0]->hook_name);
        $this->assertSame(10, $hook[0]->priority);
        $this->assertSame($new_callback, $hook[0]->callback['function'][1]);
    }
}