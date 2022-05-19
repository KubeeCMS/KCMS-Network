<?php

namespace WP_Ultimo\Dependencies;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WP_Ultimo\\Dependencies\\League\\Uri\\normalize')) {
    require __DIR__ . '/Modifiers/functions.php';
    require __DIR__ . '/functions.php';
}
