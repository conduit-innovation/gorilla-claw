<?php

namespace MonkeyHook\Test;

use MonkeyHook\Types\WPFilterTestCase;
use MonkeyHook\Mock\MockClass;

use function MonkeyHook\find_filters;
use function MonkeyHook\add_filters;

final class APITest extends WPFilterTestCase {

    protected function setUp(): void {
        parent::setUp();
        $test_object = new MockClass();

        add_filter('test_1', [$test_object, 'test'], 10, 1);
        add_filter('test_1', $this->test_closure);
        add_filter('test_1', 'MonkeyHook\Mock\MockClass::test_static_1');
        MockClass::register_with_static('test_1');
        
        add_filter('test_2', [$test_object, 'test'], 10, 1);
        add_filter('test_2', [$test_object, 'test'], 20, 1);

        add_filter('no_op', 'some_function');
        add_filter('no_op_2', 'some_function');
    }
    
    protected function tearDown(): void {
        global $wp_filter;
        $wp_filter = [];
    }

    public function testFindHookBasic() {
        $hooks = find_filters('no_op');
        $this->assertCount(1, $hooks);
        $this->assertSame('no_op', $hooks[0]->hook_name);

        $hooks = find_filters('no_op no_op_2');
        $this->assertCount(2, $hooks);

        $hooks = find_filters(['no_op', 'no_op_2']);
        $this->assertCount(2, $hooks);
    }

    public function testFindHookByFunctionName() {
        $hooks = find_filters('no_op', 'some_function');
        $this->assertCount(1, $hooks);
        $this->assertSame('no_op', $hooks[0]->hook_name);
    }

    public function testFindHookByClosure() {
        $hooks = find_filters('test_1', $this->test_closure);
        $this->assertCount(1, $hooks);
        $this->assertSame('test_1', $hooks[0]->hook_name);
    }

    public function testFindHookByObjectMethod() {
        $hooks = find_filters('test_1', ['MonkeyHook\Mock\MockClass', 'test']);
        $this->assertCount(1, $hooks);
        $this->assertSame('test_1', $hooks[0]->hook_name);
    }

    public function testFindHookByStaticMethod() {
        $hooks = find_filters('test_1', 'MonkeyHook\Mock\MockClass::test_static_1');
        $this->assertCount(1, $hooks);
        $this->assertSame('MonkeyHook\Mock\MockClass', $hooks[0]->that);
    }

    public function testFindHookByStaticMethodRegisteredWithString() {
        $hooks = find_filters('test_1', 'MonkeyHook\Mock\MockClass::test_static_1');
        $this->assertCount(1, $hooks);
        $this->assertSame('MonkeyHook\Mock\MockClass', $hooks[0]->that);
    }

    public function testFindHookNotFound() {
        $hooks = find_filters('invalid_hook');
        $this->assertCount(0, $hooks);
    }
}