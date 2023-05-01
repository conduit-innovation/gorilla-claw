<?php

namespace GorillaClaw\Test;

use GorillaClaw\Types\WPFilterTestCase;
use GorillaClaw\Hook;
use GorillaClaw\Mock\MockClass;

use function GorillaClaw\find_filters;

function dummy_func($input) { return $input; }

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

    public function testHookExists() {
        $test_object = new MockClass();
        add_filter('test_replace', [$test_object, 'get_id']);

        $hooks = find_filters('test_replace', ['GorillaClaw\Mock\MockClass', 'get_id']);
        $this->assertEquals(true, $hooks->exists());
    }

    public function testHookReplace() {
        $test_object = new MockClass();
        add_filter('test_replace', [$test_object, 'get_id']);

        $hooks = find_filters('test_replace', ['GorillaClaw\Mock\MockClass', 'get_id']);

        $hooks->replace(function($input) {
            $this->{'id'} = 2; // Equivalent to $this->id, but linter complains otherwise
            return 'replaced';
        });

        $this->assertEquals('replaced', apply_filters('test_replace', 0));
        $this->assertEquals(2, $test_object->id);
    }

    public function testHookReplaceStatic() {

        add_filter('test_1', ['GorillaClaw\Mock\MockClass', 'test_static']);
        MockClass::register_with_static('test_1');

        $hook = find_filters('test_1', 'GorillaClaw\Mock\MockClass::test_static');
        
        $hook->replace(function($input) {
            return MockClass::$static_prop;
        });

        $this->assertSame('GorillaClaw\Mock\MockClass', $hook[0]->that);

        $this->assertSame('static', apply_filters('test_1', 'input'));
    }

    public function testHookReplaceNonObject() {

        add_filter('test_1', 'var_dump');

        $hook = find_filters('test_1');
        
        $hook->replace(function($input) {
            return 'non-object';
        });

        $this->assertSame('non-object', apply_filters('test_1', 'input'));
    }

    public function testHookRebind() {
        $test_object = new MockClass();

        add_filter('test_rebind', [$test_object, 'get_id'], 10);
        
        $hooks = find_filters('test_rebind');

        $hooks[0]->rebind('test_rebind', function($input) {
            $this->{'id'} = 2; // Equivalent to $this->id, but linter complains otherwise
            return $input;
        }, 20);

        add_filter('test_rebind', [$test_object, 'get_id'], 30);
        $this->assertEquals(2, apply_filters('test_rebind', 0));
    }

    public function testHookRebindFromCollection() {
        $test_object = new MockClass();

        add_filter('test_rebind', [$test_object, 'get_id'], 10);
        
        $hooks = find_filters('test_rebind');
        
        $this->expectException('\ErrorException');
        $hooks->rebind('test_rebind', function() {}, 20);
    }

    public function testHookRebindFromNonObject() {
        add_filter('test_rebind', 'var_dump');
        
        $hooks = find_filters('test_rebind');
        
        $this->expectException('\ErrorException');
        $hooks[0]->rebind('test_rebind', function() {}, 20);
    }

    public function testHookRemove() {
        $test_object = new MockClass();

        add_filter('test_remove', [$test_object, 'get_id'], 10);
        
        $hooks = find_filters('test_remove');

        $hooks->remove();

        $this->assertEquals(1, apply_filters('test_remove', 1));
    }

    public function testHookBeforeAfter() {
        $test_object = new MockClass();

        add_filter('test_inject', [$test_object, 'test'], 10);
        
        $hooks = find_filters('test_inject');

        $hooks->inject(function($input) {
            $this->{'id'} = 1;
            return $input . '-before-' . $this->{'get_private_id'}();
        }, function($input) {
            $this->{'id'} = 2;
            return $input . '-after-' . $this->{'get_id'}();
        });

        $this->assertEquals('test-before-1-obj-after-2', apply_filters('test_inject', 'test'));

        add_filter('test_inject_2', [$test_object, 'test'], 10);
        
        $hooks = find_filters('test_inject_2');

        $hooks->inject(false, function($input) {
            return $input . '-after';
        });

        $this->assertEquals('test-obj-after', apply_filters('test_inject_2', 'test'));

        add_filter('test_inject_3', [$test_object, 'test'], 10);
        
        $hooks = find_filters('test_inject_3');

        $hooks->inject(function($input) {
            return $input . '-before';
        });

        $this->assertEquals('test-before-obj', apply_filters('test_inject_3', 'test'));
    }

    public function testHookBeforeAfterNoBinding() {
        $test_object = new MockClass();

        add_filter('test_inject', function($input) { return $input; }, 10);
        
        $hooks = find_filters('test_inject');

        $hooks->inject(function($input) {
            return $input . '-before';
        }, function($input) {
            return $input . '-after';
        });

        $this->assertEquals('test-before-after', apply_filters('test_inject', 'test'));

    }

    public function testHookBeforeAfterNoBindingPlain() {
        $test_object = new MockClass();

        add_filter('test_inject', __NAMESPACE__. '\dummy_func', 10);
        
        $hooks = find_filters('test_inject');

        $hooks->inject(function($input) {
            return $input . '-before';
        }, function($input) {
            return $input . '-after';
        });

        $this->assertEquals('test-before-after', apply_filters('test_inject', 'test'));

    }

    public function testTolerateBrokenWpFilter() {
        global $wp_filter;

        $test_object = new MockClass();

        add_filter('test_broken', [$test_object, 'get_id'], 10);

        $hooks = find_filters('test_broken');

        $wp_filter = [];

        $hooks->replace(function($input) {
            return 'replaced';
        });

        $this->assertEquals(0, apply_filters('test_broken', 0));
    }


}