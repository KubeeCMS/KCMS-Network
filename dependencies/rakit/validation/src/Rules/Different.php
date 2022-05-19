<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class Different extends Rule
{
    /** @var string */
    protected $message = "The :attribute must be different with :field";
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
        $anotherValue = $this->validation->getValue($field);
        return $value != $anotherValue;
    }
}
