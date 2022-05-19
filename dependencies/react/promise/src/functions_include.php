<?php

namespace WP_Ultimo\Dependencies;

if (!\function_exists('React\\Promise\\resolve')) {
    require __DIR__ . '/functions.php';
}
