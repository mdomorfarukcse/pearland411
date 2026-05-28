<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

// Auto-inject a hidden CSRF input into every <form method="POST"> in the
// rendered output, and add a <meta name="csrf-token"> tag to <head>.
// This lets us turn on CI3's CSRF protection globally without manually
// patching ~125 raw <form> tags scattered across the views.
$hook['display_override'][] = array(
    'class'    => '',
    'function' => 'csrf_inject_into_output',
    'filename' => 'Csrf_inject.php',
    'filepath' => 'hooks',
    'params'   => array(),
);
