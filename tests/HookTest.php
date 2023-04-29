<?php

namespace MonkeyHook\Test;

use PHPUnit\Framework\TestCase;
use MonkeyHook\Hook;
use function MonkeyHook\find_filters;
use stdClass;

use MonkeyHook\Mock\MockClass;

final class HookTest extends TestCase {

    private $test_closure;

    protected function setUp(): void {

        $this->test_closure = function($input){ return $input; };

        add_filter('make_uppercase', $this->test_closure);
        add_filter('make_uppercase', ['MonkeyHook\Mock\MockClass', 'make_uppercase_static']);
        MockClass::register_with_static('make_uppercase');
    }

    protected function tearDown(): void {
        global $wp_filter;
        $wp_filter = [];
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

        $hook = find_filters('hook_name');
        $this->assertCount(1, $hook);
        $new_callback = function () {};
        $hook->replace($new_callback);
        $this->assertSame('hook_name', $hook[0]->hook_name);
        $this->assertSame(10, $hook[0]->priority);
        $this->assertSame($new_callback, $hook[0]->callback['function'][1]);
    }

    public function testHookReplaceStatic() {
        $hook = find_filters('make_uppercase', ['MonkeyHook\Mock\MockClass', 'make_uppercase_static']);
        
        $hook->replace(function($input) {
            return MockClass::$static_prop;
        });

        $this->assertSame('MonkeyHook\Mock\MockClass', $hook[0]->that);

        $this->assertSame('static', apply_filters('make_uppercase', 'lowercase'));
    }

}