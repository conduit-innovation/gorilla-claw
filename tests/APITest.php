<?php

namespace MonkeyHook\Test;

use PHPUnit\Framework\TestCase;
use stdClass;

use function MonkeyHook\find_filters;
use function MonkeyHook\add_filters;

use MonkeyHook\Mock\MockClass;

final class APITest extends TestCase {

    private $test_closure;

    protected function setUp(): void {
        $test_object = new MockClass();

        $this->test_closure = function($input){ return $input . '-closure';};

        add_filters('make_uppercase', [$test_object, 'make_uppercase_object'], 10, 1);
        add_filter('make_lowercase', [$test_object, 'make_lowercase'], 10, 1);
        add_filter('make_lowercase', [$test_object, 'make_lowercase'], 20, 1);

        add_filter('no_op', 'some_function');
        add_filter('no_op_2', 'some_function');

        add_filter('make_uppercase', $this->test_closure);
        add_filters('make_uppercase', 'MonkeyHook\Mock\MockClass::make_uppercase_static_1');
        MockClass::register_with_static('make_uppercase');
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
        $hooks = find_filters('make_uppercase', $this->test_closure);
        $this->assertCount(1, $hooks);
        $this->assertSame('make_uppercase', $hooks[0]->hook_name);
    }

    public function testFindHookByObjectMethod() {
        $hooks = find_filters('make_uppercase', ['MonkeyHook\Mock\MockClass', 'make_uppercase_object']);
        $this->assertCount(1, $hooks);
        $this->assertSame('make_uppercase', $hooks[0]->hook_name);
    }

    public function testFindHookByStaticMethod() {
        $hooks = find_filters('make_uppercase', 'MonkeyHook\Mock\MockClass::make_uppercase_static');
        $this->assertCount(1, $hooks);
        $this->assertSame('MonkeyHook\Mock\MockClass', $hooks[0]->that);
    }

    public function testFindHookByStaticMethodRegisteredWithString() {
        $hooks = find_filters('make_uppercase', 'MonkeyHook\Mock\MockClass::make_uppercase_static_1');
        $this->assertCount(1, $hooks);
        $this->assertSame('MonkeyHook\Mock\MockClass', $hooks[0]->that);
    }

    public function testFindHookNotFound() {
        $hooks = find_filters('invalid_hook');
        $this->assertCount(0, $hooks);
    }
}