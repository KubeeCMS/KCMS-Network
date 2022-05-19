<?php

namespace WP_Ultimo\Dependencies;

// Don't redefine the functions if included multiple times.
if (!\function_exists('WP_Ultimo\\Dependencies\\League\\Uri\\resolve_domain')) {
    require __DIR__ . '/functions.php';
}
