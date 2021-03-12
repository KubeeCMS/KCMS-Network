<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Helper;
use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class In extends \WP_Ultimo\Dependencies\Rakit\Validation\Rule
{
    /** @var string */
    protected $message = "The :attribute only allows :allowed_values";
    /** @var bool */
    protected $strict = \false;
    /**
     * Given $params and assign the $this->params
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params) : \WP_Ultimo\Dependencies\Rakit\Validation\Rule
    {
        if (\count($params) == 1 && \is_array($params[0])) {
            $params = $params[0];
        }
        $this->params['allowed_values'] = $params;
        return $this;
    }
    /**
     * Set strict value
     *
     * @param bool $strict
     * @return void
     */
    public function strict(bool $strict = \true)
    {
        $this->strict = $strict;
    }
    /**
     * Check $value is existed
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        $this->requireParameters(['allowed_values']);
        $allowedValues = $this->parameter('allowed_values');
        $or = $this->validation ? $this->validation->getTranslation('or') : 'or';
        $allowedValuesText = \WP_Ultimo\Dependencies\Rakit\Validation\Helper::join(\WP_Ultimo\Dependencies\Rakit\Validation\Helper::wraps($allowedValues, "'"), ', ', ", {$or} ");
        $this->setParameterText('allowed_values', $allowedValuesText);
        return \in_array($value, $allowedValues, $this->strict);
    }
}
