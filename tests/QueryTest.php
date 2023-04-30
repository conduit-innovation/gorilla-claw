<?php

namespace MonkeyHook\Test;

use MonkeyHook\Types\WPFilterTestCase;
use MonkeyHook\Hook;
use MonkeyHook\Mock\MockClass;

use function MonkeyHook\find_filters;

final class QueryTest extends WPFilterTestCase {
    protected function setUp(): void {
        parent::setUp();
    }

    public function testFindNothing() {
        $hooks = find_filters('nothing');
        $this->assertCount(0, $hooks);
    }

    public function testFindAllCallbackTypes() {
        $test_object = new MockClass();

        add_filter('test', function() {});
        add_filter('test', 'test');
        add_filter('test', [$test_object, 'test']);

        add_filter('test', ['MonkeyHook\Mock\MockClass', 'test_static'], 10);
        add_filter('test', 'MonkeyHook\Mock\MockClass::test_static', 20);

        $hooks = find_filters('test');
        $this->assertCount(5, $hooks);
    }

    public function testFindStaticNormalisation() {
        $test_object = new MockClass();

        add_filter('test', ['MonkeyHook\Mock\MockClass', 'test_static'], 10);
        add_filter('test', 'MonkeyHook\Mock\MockClass::test_static', 20);

        $hooks = find_filters('test', 'MonkeyHook\Mock\MockClass::test_static');
        $this->assertCount(2, $hooks);
    }

    public function testFindClosure() {
        add_filter('test', $this->test_closure);

        $hooks = find_filters('test', $this->test_closure);
        $this->assertCount(1, $hooks);
    }

    public function testFindAllObjectMethods() {
        $test_object = new MockClass();

        add_filter('test', [$test_object, 'test']);
        add_filter('test', [$test_object, 'test_1']);

        $hooks = find_filters('test', ['MonkeyHook\Mock\MockClass', false]);
        $this->assertCount(2, $hooks);
    }

    public function testFindAllStaticMethods() {
        add_filter('test', ['MonkeyHook\Mock\MockClass', 'test_static'], 10);
        add_filter('test', 'MonkeyHook\Mock\MockClass::test_static', 20);

        $hooks = find_filters('test', 'MonkeyHook\Mock\MockClass::');
        $this->assertCount(2, $hooks);
    }

}