<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class Same extends \WP_Ultimo\Dependencies\Rakit\Validation\Rule
{
    /** @var string */
    protected $message = "The :attribute must be same with :field";
    /** @var array */
    protected $fillableParams = ['field'];
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        $this->requireParameters($this->fillableParams);
        $field = $this->parameter('field');
        $anotherValue = $this->getAttribute()->getValue($field);
        return $value == $anotherValue;
    }
}