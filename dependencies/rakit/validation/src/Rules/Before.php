<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class Before extends \WP_Ultimo\Dependencies\Rakit\Validation\Rule
{
    use Traits\DateUtilsTrait;
    /** @var string */
    protected $message = "The :attribute must be a date before :time.";
    /** @var array */
    protected $fillableParams = ['time'];
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws \Exception
     */
    public function check($value) : bool
    {
        $this->requireParameters($this->fillableParams);
        $time = $this->parameter('time');
        if (!$this->isValidDate($value)) {
            throw $this->throwException($value);
        }
        if (!$this->isValidDate($time)) {
            throw $this->throwException($time);
        }
        return $this->getTimeStamp($time) > $this->getTimeStamp($value);
    }
}
