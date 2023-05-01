<?php

namespace GorillaClaw;

/**
 * find_actions
 *
 * @param  mixed $hook_name
 * @param  mixed $callback
 * @return HookCollection
 */

function find_actions(string | array $hook_name, mixed $callback = false): HookCollection {
    return find_filters($hook_name, $callback);
}

/**
 * find_filters
 *
 * @param  mixed $hook_name
 * @param  mixed $callback
 * @return HookCollection
 */

function find_filters(string | array $hook_name, mixed $callback = false): HookCollection {
    global $wp_filter;
    return (new Query($wp_filter))->find($hook_name, $callback);
}

/**
 * add_actions
 *
 * @param  mixed $hook_name
 * @param  mixed $callback
 * @param  mixed $priority
 * @param  mixed $accepted_args
 * @return void
 */

function add_actions(string | array $hook_name, $callback, $priority = 10, $accepted_args = 1): void {
    add_filters($hook_name, $callback, $priority, $accepted_args);
}

/**
 * add_filters
 *
 * @param  mixed $hook_names
 * @param  mixed $callback
 * @param  mixed $priority
 * @param  mixed $accepted_args
 * @return void
 */

function add_filters(string | array $hook_names, $callback, $priority = 10, $accepted_args = 1): void {
    if(is_string($hook_names)) {
        $hook_names = explode(" ", $hook_names);
    }

    foreach($hook_names as $hook_name) {
        add_filter($hook_name, $callback, $priority, $accepted_args);
    }
}