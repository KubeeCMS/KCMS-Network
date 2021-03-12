<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation\Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
class RequiredIf extends \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Required
{
    /** @var bool */
    protected $implicit = \true;
    /** @var string */
    protected $message = "The :attribute is required";
    /**
     * Given $params and assign the $this->params
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params) : \WP_Ultimo\Dependencies\Rakit\Validation\Rule
    {
        $this->params['field'] = \array_shift($params);
        $this->params['values'] = $params;
        return $this;
    }
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value) : bool
    {
        $this->requireParameters(['field', 'values']);
        $anotherAttribute = $this->parameter('field');
        $definedValues = $this->parameter('values');
        $anotherValue = $this->getAttribute()->getValue($anotherAttribute);
        $validator = $this->validation->getValidator();
        $requiredValidator = $validator('required');
        if (\in_array($anotherValue, $definedValues)) {
            $this->setAttributeAsRequired();
            return $requiredValidator->check($value, []);
        }
        return \true;
    }
}
