<?php

namespace MonkeyHook;

function find_actions(string | array $hook_name, mixed $callback = false) {
    return find_filters($hook_name, $callback);
}

function find_filters(string | array $hook_name, mixed $callback = false) {
    global $wp_filter;
    return (new Query($wp_filter))->find($hook_name, $callback);
}

function add_actions(string | array $hook_name, $callback, $priority, $accepted_args) {
    return add_filters($hook_name, $callback, $priority, $accepted_args);
}

function add_filters(string | array $hook_names, $callback, $priority, $accepted_args) {
    if(is_string($hook_names)) {
        $hook_names = explode(" ", $hook_names);
    }

    foreach($hook_names as $hook_name) {
        add_filter($hook_name, $callback, $priority, $accepted_args);
    }
}