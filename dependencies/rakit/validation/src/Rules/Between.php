<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class Between extends \WP_Ultimo\Dependencies\Rakit\Validation\Rule
{
    use Traits\SizeTrait;
    /** @var string */
    protected $message = "The :attribute must be between :min and :max";
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
        $min = $this->getBytesSize($this->parameter('min'));
        $max = $this->getBytesSize($this->parameter('max'));
        $valueSize = $this->getValueSize($value);
        if (!\is_numeric($valueSize)) {
            return \false;
        }
        return $valueSize >= $min && $valueSize <= $max;
    }
}
