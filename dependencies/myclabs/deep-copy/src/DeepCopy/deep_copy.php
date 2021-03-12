<?php

namespace WP_Ultimo\Dependencies\DeepCopy;

use function function_exists;
if (\false === \function_exists('WP_Ultimo\\Dependencies\\DeepCopy\\deep_copy')) {
    /**
     * Deep copies the given value.
     *
     * @param mixed $value
     * @param bool  $useCloneMethod
     *
     * @return mixed
     */
    function deep_copy($value, $useCloneMethod = \false)
    {
        return (new \WP_Ultimo\Dependencies\DeepCopy\DeepCopy($useCloneMethod))->copy($value);
    }
}
