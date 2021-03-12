<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class AlphaNum extends \WP_Ultimo\Dependencies\Rakit\Validation\Rule
{
    /** @var string */
    protected $message = "The :attribute only allows alphabet and numeric";
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        if (!\is_string($value) && !\is_numeric($value)) {
            return \false;
        }
        return \preg_match('/^[\\pL\\pM\\pN]+$/u', $value) > 0;
    }
}
