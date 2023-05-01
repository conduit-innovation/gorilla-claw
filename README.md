# GorillaClaw
![Build](https://img.shields.io/github/actions/workflow/status/conduit-innovation/gorilla-claw/php.yml?style=flat)
[![Coverage](https://raw.githubusercontent.com/conduit-innovation/gorilla-claw/image-data/coverage.svg)](https://conduit-innovation.github.io/gorilla-claw/coverage)
[![API Doc](https://img.shields.io/badge/api--docs-incomplete-yellow)](https://conduit-innovation.github.io/gorilla-claw/api)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/conduit/gorilla-claw/php)
![Wordpress Compatibility](https://img.shields.io/badge/wordpress->=5.9-blue)

WordPress action / filter runtime toolkit library, with monkey-patching capabilities.

- **[Locate](#user-content-locating)** - Easily find hook handlers, by filter, function name, object name and method
- **[Replace](#user-content-replacement)** - Replace hook callbacks in one line, *even use the original callback's `$this` via magic scope binding*
- **[Rebind](#user-content-rebinding)** - Add new handlers, bound to another's scope (see 'rebind')
- **[Inject](#user-content-injection)** - Inject pre and post processors to existing handlers, with scope binding
- Manage hook handlers as a collection
- `add_filters()` and `add_actions()` for attaching to multiple hooks in a single command
- Uses fast Closures, not slow Reflection.
- *[Coming Soon] Profile individual handlers, or the entire hook*

The intention is that this library will be safe to use in production, with minimal performance overhead. However, until the `1.0.0` release, this should only be used for testing. But there is already [full test coverage for releases](https://conduit-innovation.github.io/gorilla-claw/coverage).

## Installation

Via `composer`:

```bash
composer require conduit/gorilla-claw
```
## What is this?

GorillaClaw is a runtime toolkit library for manipulating WordPress hooks; aka. actions and filters. It's intended to help end-user developers make plugins work with each other when conflicting or misbehaving.

It should not be used as a component in a distributed WordPress plugin, use the normal WordPress Hook API for that. It also isn't intended as a 'new sparkly hook wrapper', that nobody needs or wants.

A unique feature of GorillaClaw is that it automatically binds your hook handlers to existing (and previously uncontrollable) objects that may lay nestled in another plugin's code, by monkey-patching scope. It even breaks into `private` and `protected` properties, and it also allows you to call any method, with _likely disasterous consequences_.

> :warning: **Here start the warnings. They're deliberately peppered throughout.**

> :warning: _**Ignore them at your peril.**_

As well as the dangerous features, the library contains some useful functionality for locating handlers and replacing or removing them (safely).

**GorillaClaw can be used for good. It can also be used for evil.**

## Locating

**Danger Level: :dark_sunglasses: Safe**

Find handlers using a number of  different queries. Easily locate specific classes of handler.

`find_filters()` returns a collection of `Hook`s, which is the basis for all hook manipulation in GorillaClaw.

```php
use function GorillaClaw\find_filters;
use function GorillaClaw\find_actions;

/* Find all handlers for `your_action` */
$hooks = find_filters('your_action');

/* Find all `function_name` handlers for `your_action` */
$hooks = find_filters('your_action', 'function_name');

/* Find all matching static handlers for `your_action` */
$hooks = find_filters('your_action', 'Namespace\ClassName::static_method');

/* Find all matching object / class handlers for `your_action` */
$hooks = find_filters('your_action', ['Namespace\ClassName', 'method_name']);

/* Find all object / class handlers for `your_action` matching any method */
$hooks = find_filters('your_action', ['Namespace\ClassName', false]);

/* You can also find using multiple hook names */
$hooks = find_filters('your_action another_action');
/* or */
$hooks = find_filters(['your_action', 'another_action']);


/* $hooks are a collection of handlers */
foreach($hooks as $hook) {
    
    /* Do something with individual handler */
    $hook->remove();
}

/* ... and array accessible */
$hooks[2]->remove();
```

## Removal

**Danger Level: :dark_sunglasses: Safe**

Un-hook / remove handlers found with `find_*****s()`. Safe and simple.

```php
use function GorillaClaw\find_filters;

$hooks = find_filters('your_action');

/* Remove all matching handlers */
$hooks->remove();

/* Remove first handler */
$hooks[0]->remove();
```

## Replacement

**Danger Level: :thinking: Be careful**

OK, here's where it starts to get sketchy. We can `replace` handlers with our own closures, but magically, `$this` will be proxied to the original object. We can even read *and write* protected or private properties, and call methods similarly.

```php
use function GorillaClaw\find_filters;

/* Our dummy class for the examples below: */

class SomeClass {
    private $private_property;
    public $public_property;

    private function hello($name) {
        return "Hello " . $name;
    }

    public function public_method() {}
}

$hooks = find_filters('your_action', ['SomeClass', 'some_method']);

/* Replace a handler, magically binding to the original object */

$hooks->replace(function($input, $any, $other, $args) {
    /* $this is now the original object, and we've been monkey-patched into scope */

    $var = $this->public_method();
    $var = $this->public_property;

    /* Call a (!) private (!) method */
    return str_replace("Hello", "Goodbye", $this->hello());
});
```
> :warning: **Properties are _writeable_, even if `protected` or `private` or `final class`.**

> :warning: **Methods called by the replaced handler may change object state for subsequent calls for this action or others relying on the original object.** This can cause unpredictable behaviour in most cases.

Lots of headaches will most likely occur if you change the class state in some way. Although in extreme cases, this is desired. **If you can in anyway avoid doing this, avoid it.**

The actual mechanism of doing this is [quite unusual](https://github.com/conduit-innovation/gorilla-claw/blob/624ec24906777341156b026dc100788622d3f42c/src/class/HookProxy.php#L34-L45), using `Closure` and scope binding and passing by reference. No slow `Reflection` is used.

## Rebinding

**Danger Level: :warning: Probably a bad idea**

Rebinding is similar to replacement, except the original filter handler is left active. This allows us to 'tap-in' to an existing filter handler's `$this` and scope.

```php
use function GorillaClaw\find_filters;

$hooks = find_filters('your_action');

$hooks[0]->rebind('another_action', function() {
    
    /* 
     * $this now points to the 'your_action' handler's $this,
     * but we are currently running on 'another_action'
     */
    
    return $this->foo;
}, 10);

/* Re-binding a collection of hooks throws an exception */

$hooks->rebind('another_action', function() {});
//||\\ <-- Note: '$hooks', not '$hook[0]' 

```

> :warning: **Methods called by the re-bound handler may change object state for subsequent calls for this action or others relying on the original object.** This can cause unpredictable behaviour in most cases.

> :warning: **Rebinding can not guarantee the order in which the re-bound function and the original execute.** That's entirely down to the application logic. As the objects are linked, both handlers can affect each other.

> :thinking: **Setting up and tearing down modifications to an object on a re-bound handler using _[Injection](#user-content-injection)_ can mitigate some of the risks above.**

## Injection

**Danger Level: :warning: Probably a bad idea**

Injection allows you to add functions executed before and after a single filter handler. You may access `$this` and even modify private variables.

Most of the time, if we want a function to run before an existing handler, we just add it with a 'lower' priority number, and 'higher' to run after. *This is the right way.* However, some times we just want to manipulate a single handler, like maybe ...ugh.. call methods that change the object state, so it makes sense to `modify -> run the original -> un-modify`, so future interactions with the object remain unaffected.

Both before and after callbacks are optional, and feed through their arguments and return values in the usual chained WordPress way.

```php
use function GorillaClaw\find_filters;

$hooks = find_filters('your_action');

$hooks->inject(function($input) {
    return $input . '-run-before';
}, function($input) {
    return $input . '-run-after';
});
```

> :warning: **Methods called by the injected handlers may change object state for subsequent calls for this action or others relying on the original object.** Remember to tear down modifications to minimise the chance of this occuring.

> :warning: **Rebinding can not guarantee the order in which the re-bound function and the original execute.** That's entirely down to the application logic. As the objects are linked, both handlers can affect each other.

## Adding

There is also a simple wrapper around `add_filter()` and `add_action()` - plural versions allowing handlers to be added to multiple actions / filters in one line. It's just syntactic sugar.

```php
use function GorillaClaw\add_filters;
use function GorillaClaw\add_actions;

add_filters('filter_1 filter_2', 'some_function', 10, 2);
add_filters(['filter_1', 'filter_2'], 'some_function', 10, 2);

add_actions('action_1 action_2', 'some_function', 10, 2);
add_actions(['action_1', 'action_2'], 'some_function', 10, 2);
```

## You don't need this

Ideally, you should never 'need' this library. 

However, with plugin developers making use of objects more frequently (good!), and not always being mindful of scope limitations (bad), you can sometimes need to patch-in to a plugin object's scope and work with private variables / methods. Some examples of this include plugins that set up Gutenberg Blocks, and plugins that use dependency injection.

**If you can achieve your goal without this library, then do that instead.**

Runtime monkey-patching can cause a whole load of debugging hassle if done incorrectly, so please be very careful if modifying object properties whether using `replace`, `rebind` or `inject`.

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)

---
_Turn off your linters, throw away your test suite, encrypt your codebase! It's time to break shit..._
