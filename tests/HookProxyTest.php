<?php namespace MonkeyHook\Test;

use PHPUnit\Framework\TestCase;
use MonkeyHook\HookProxy;
use MonkeyHook\Mock\MockClass;

final class HookProxyTest extends TestCase {
    public function testCallback() {

        $cb = function() {
            return 'ok';
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('ok', $proxy->__cb());
    }

    public function testCallbackPassthrough() {

        $cb = function($input) {
            return strtoupper($input);
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('OK', $proxy->__cb('ok'));
    }

    public function testThisAccess() {

        $cb = function($input) {
            $this->{'id'} ++;
            return $input . '-' . $this->{'id'};
        };

        $obj = new MockClass(1);

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('id-2', $proxy->__cb('id'));
        $this->assertEquals('id-3', $proxy->__cb('id'));
    }

    public function testThisCall() {

        $cb = function($input) {
            return $this->{'test'}($input);
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('this-obj', $proxy->__cb('this'));
    }

    public function testStaticCall() {

        $cb = function($input) {
            return static::{'test_static'}($input);
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('this-static', $proxy->__cb('this'));
    }

    public function testPrivateThisCall() {

        $cb = function($input) {
            return $input . '-' . $this->{'private_id'};
        };

        $obj = new MockClass(1);

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('id-1', $proxy->__cb('id'));
    }

    public function testPrivateThisMethodCall() {

        $cb = function($input) {
            return $input . '-' . $this->{'get_private_id'}();
        };

        $obj = new MockClass(1);

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('id-1', $proxy->__cb('id'));
    }
}