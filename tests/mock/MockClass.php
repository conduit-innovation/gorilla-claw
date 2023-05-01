<?php

namespace GorillaClaw\Mock;

class MockClass {

    public static $static_prop = 'static';
    public $id;
    private $private_id;

    function __construct($id = 1) {
        if($id) {
            $this->id = $id;
            $this->private_id = $id;
        }
    }

    public function get_id() {
        return $this->id;
    }

    private function get_private_id() {
        return $this->id;
    }

    public static function register_with_static($action) {
        add_filter($action, [static::class, 'test_static']);
    }

    public function test($input) {
        return $input . '-obj';
    }

    public function test_1($input) {
        return $input . '-obj_1';
    }

    public function test_static($input) {
        return $input . '-static';
    }

    public function test_static_1($input) {
        return $input . '-static_1';
    }

    private function test_private($input) {
        return $input . '-private';
    }
}
