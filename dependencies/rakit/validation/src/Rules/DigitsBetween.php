<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class DigitsBetween extends \WP_Ultimo\Dependencies\Rakit\Validation\Rule
{
    /** @var string */
    protected $message = "The :attribute must have a length between the given :min and :max";
    /** @var array */
    protected $fillableParams = ['min', 'max'];
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        $this->requireParameters($this->fillableParams);
        $min = (int) $this->parameter('min');
        $max = (int) $this->parameter('max');
        $length = \strlen((string) $value);
        return !\preg_match('/[^0-9]/', $value) && $length >= $min && $length <= $max;
    }
}
