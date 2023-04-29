<?php

namespace MonkeyHook\Test;

use PHPUnit\Framework\TestCase;
use stdClass;

use function MonkeyHook\find_filters;

final class APITest extends TestCase {

    private $test_closure;

    protected function setUp(): void {
        $object_hooker = new stdClass();
        $object_hooker->make_uppercase_function = function($input) {
            return strtoupper($input);
        };
        $object_hooker->make_lowercase_function = function($input) {
            return strtolower($input);
        };

        $this->test_closure = function(){};

        add_filter('make_uppercase', [$object_hooker, 'make_uppercase_function'], 10, 1);
        add_filter('make_lowercase', [$object_hooker, 'make_lowercase_function'], 20, 1);

        add_filter('make_uppercase', 'basic_global_make_uppercase');
        add_filter('make_uppercase', $this->test_closure);
    }

    public function testFindHookBasic() {
        $hook = find_filters('make_uppercase');
        $this->assertSame('make_uppercase', $hook[0]->hook_name);

        $hook = find_filters('make_uppercase make_lowercase');
        $this->assertSame('make_uppercase', $hook[0]->hook_name);

        $hook = find_filters(['make_uppercase', 'make_lowercase']);
    }

    public function testFindHookByFunctionName() {
        $hook = find_filters('make_uppercase', 'basic_global_make_uppercase');
        $this->assertSame('make_uppercase', $hook[0]->hook_name);
    }

    public function testFindHookByClosure() {
        $hook = find_filters('make_uppercase', $this->test_closure);
        $this->assertSame('make_uppercase', $hook[0]->hook_name);
    }

    public function testFindHookByObjectMethod() {
        $hook = find_filters('make_uppercase', ['stdClass', 'make_uppercase_function']);
        $this->assertSame('make_uppercase', $hook[0]->hook_name);
    }

    public function testFindHookNotFound() {
        $hook = find_filters('make_uppercase', ['stdClass', 'make_uppercase_function']);
        $this->assertSame('make_uppercase', $hook[0]->hook_name);
    }
}