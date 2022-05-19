<?php

namespace WP_Ultimo\Dependencies;

if (\PHP_VERSION_ID < 80000) {
    class ValueError extends \Error
    {
    }
}
