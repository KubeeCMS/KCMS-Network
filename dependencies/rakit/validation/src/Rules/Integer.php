<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class Integer extends Rule
{
    /** @var string */
    protected $message = "The :attribute must be integer";
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        return \filter_var($value, \FILTER_VALIDATE_INT) !== \false;
    }
}
