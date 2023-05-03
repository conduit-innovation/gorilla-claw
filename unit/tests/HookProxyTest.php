<?php namespace GorillaClaw\Test;

use PHPUnit\Framework\TestCase;
use GorillaClaw\HookProxy;
use GorillaClaw\Mock\MockClass;

final class HookProxyTest extends TestCase {
    public function testCallback() {

        $cb = function() {
            return 'ok';
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('ok', $proxy->___cb());
    }

    public function testCallbackPassthrough() {

        $cb = function($input) {
            return strtoupper($input);
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('OK', $proxy->___cb('ok'));
    }

    public function testThisAccess() {

        $cb = function($input) {
            $this->{'id'} ++;
            return $input . '-' . $this->{'id'};
        };

        $obj = new MockClass(1);

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('id-2', $proxy->___cb('id'));
        $this->assertEquals('id-3', $proxy->___cb('id'));
    }

    public function testThisCall() {

        $cb = function($input) {
            return $this->{'test'}($input);
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('this-obj', $proxy->___cb('this'));
    }

    public function testStaticCall() {

        $cb = function($input) {
            return static::{'test_static'}($input);
        };

        $obj = new MockClass();

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('this-static', $proxy->___cb('this'));
    }

    public function testPrivateThisCall() {

        $cb = function($input) {
            return $input . '-' . $this->{'private_id'};
        };

        $obj = new MockClass(1);

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('id-1', $proxy->___cb('id'));
    }

    public function testPrivateThisMethodCall() {

        $cb = function($input) {
            return $input . '-' . $this->{'get_private_id'}();
        };

        $obj = new MockClass(1);

        $proxy = new HookProxy($cb, $obj);

        $this->assertEquals('id-1', $proxy->___cb('id'));
    }
}