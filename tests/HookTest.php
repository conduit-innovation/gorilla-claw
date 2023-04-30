<?php

namespace MonkeyHook\Test;

use MonkeyHook\Types\WPFilterTestCase;
use MonkeyHook\Hook;
use MonkeyHook\Mock\MockClass;

use function MonkeyHook\find_filters;

final class HookTest extends WPFilterTestCase {

    protected function setUp(): void {
        parent::setUp();
        
    }

    public function testHookConstructor() {
        $hook = new Hook('hook_name', ['function' => [$this, 'some_func']], 10, 'function_key'); 
        $this->assertSame('hook_name', $hook->hook_name);
        $this->assertSame(10, $hook->priority);
        $this->assertSame('function_key', $hook->function_key);
        $this->assertSame($this, $hook->that);
    }

    public function testHookReplace() {
        $test_object = new MockClass();
        add_filter('test_replace', [$test_object, 'get_id']);

        $hooks = find_filters('test_replace', ['MonkeyHook\Mock\MockClass', 'get_id']);

        $hooks->replace(function($input) {
            $this->id = 2;
            return 'replaced';
        });

        $this->assertEquals('replaced', apply_filters('test_replace', 0));
        $this->assertEquals(2, $test_object->id);
    }

    public function testHookReplaceStatic() {

        add_filter('test_1', ['MonkeyHook\Mock\MockClass', 'test_static']);
        MockClass::register_with_static('test_1');

        $hook = find_filters('test_1', 'MonkeyHook\Mock\MockClass::test_static');
        
        $hook->replace(function($input) {
            return MockClass::$static_prop;
        });

        $this->assertSame('MonkeyHook\Mock\MockClass', $hook[0]->that);

        $this->assertSame('static', apply_filters('test_1', 'input'));
    }

    public function testHookRebind() {
        $test_object = new MockClass();

        add_filter('test_rebind', [$test_object, 'get_id'], 10);
        
        $hooks = find_filters('test_rebind');

        $hooks[0]->rebind('test_rebind', function($input) {
            $this->id = 2;
            return $input;
        }, 20);

        add_filter('test_rebind', [$test_object, 'get_id'], 30);
        $this->assertEquals(2, apply_filters('test_rebind', 0));
    }

    public function testHookRemove() {
        $test_object = new MockClass();

        add_filter('test_remove', [$test_object, 'get_id'], 10);
        
        $hooks = find_filters('test_remove');

        $hooks->remove();

        $this->assertEquals(1, apply_filters('test_remove', 1));
    }

}