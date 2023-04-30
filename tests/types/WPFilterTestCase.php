<?php

namespace MonkeyHook\Types;

use PHPUnit\Framework\TestCase;
use MonkeyHook\Hook;
use MonkeyHook\Mock\MockClass;

use function MonkeyHook\find_filters;

abstract class WPFilterTestCase extends TestCase {

    protected $test_closure;

    protected function setUp(): void {
        $this->test_closure = function($input){ return $input; };
    }

    protected function tearDown(): void {
        global $wp_filter;
        $wp_filter = [];
    }

}