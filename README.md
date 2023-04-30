# monkey-hook
![Build](https://img.shields.io/github/actions/workflow/status/conduit-innovation/monkey-hook/php.yml?style=flat)
![Coverage](https://raw.githubusercontent.com/conduit-innovation/monkey-hook/image-data/coverage.svg)


Coherent WordPress action / filter hook API, with monkey-patching capabilities.

- Easily find hook handlers, by filter, function name, object name and method
- Replace hook callbacks in one line, *even use the original callback's `$this` via magic scope binding*
- Add new handlers, bound to another's scope (see 'rebind')
- Inject pre and post processors to existing handlers, with scope binding
- TODO: Profile individual handlers, or the entire hook
- Manage hook handlers as a group
- `add_filters()` and `add_actions()` for attaching to multiple hooks in a single command


## Installation

Via composer:

```bash
composer require conduit/hook-monkey
```

## Locating

**Danger Level: Safe**

```php
use function MonkeyHook\find_filters;

$hooks = find_filters('your_action');
```

## Removal

**Danger Level: Safe**

```php
use function MonkeyHook\find_filters;

$hooks = find_filters('your_action');

$hooks->remove();
```

## Replacement

**Danger Level: Be careful**

```php
use function MonkeyHook\find_filters;

$hooks = find_filters('your_action');

$hooks->replace(function($input) {
    // $this will point to the original handler's $this
    return $this->hello() . ' world!';
});
```

## Rebinding

**Danger Level: Probably a bad idea**

Rebinding is similar to replacement, except the original filter handler is left active. This allows us to 'tap-in' to an existing filter handler's `$this` and scope.

```php
use function MonkeyHook\find_filters;

$hooks = find_filters('your_action');

$hooks[0]->rebind('another_action', function() {
    // $this now points to the 'your_action' handler's $this.
    return $this->foo;
}, 10)
```

## Injection

**Danger Level: Probably a bad idea**

Injection allows you to add functions executed before and after a single filter handler. You may access `$this` and even modify private variables.

Wielding great power usually is down to great ignorance. If you're using injection, it's likely you're 'doing-it-wrong', however there are some specific cases where injection can be useful.

```php
use function MonkeyHook\find_filters;

$hooks = find_filters('your_action');

$hooks->inject(function($input) {
    return $input . '-run-before';
}, function($input) {
    return $input . '-run-after';
});
```

### Adding

...tbc


## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)