<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class Email extends Rule
{
    /** @var string */
    protected $message = "The :attribute is not valid email";
    /**
     * Check $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        return \filter_var($value, \FILTER_VALIDATE_EMAIL) !== \false;
    }
}
