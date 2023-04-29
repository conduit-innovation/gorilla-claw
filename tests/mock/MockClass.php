<?php

namespace MonkeyHook\Mock;

class MockClass {

    public static $static_prop = 'static';

    public static function register_with_static($action) {
        add_filter($action, [static::class, 'make_uppercase_static']);
    }

    public static function make_uppercase_static($input) {
        return strtoupper($input);
    }

    public static function make_uppercase_static_1($input) {
        return strtoupper($input.'-1');
    }

    public function make_uppercase_object($input) {
        return strtoupper($input.'-obj');
    }
}
