<?php

namespace WP_Ultimo\Dependencies\Rakit\Validation;

class Validator
{
    use Traits\TranslationsTrait, Traits\MessagesTrait;
    /** @var array */
    protected $translations = [];
    /** @var array */
    protected $validators = [];
    /** @var bool */
    protected $allowRuleOverride = \false;
    /** @var bool */
    protected $useHumanizedKeys = \true;
    /**
     * Constructor
     *
     * @param array $messages
     * @return void
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
        $this->registerBaseValidators();
    }
    /**
     * Register or override existing validator
     *
     * @param mixed $key
     * @param \Rakit\Validation\Rule $rule
     * @return void
     */
    public function setValidator(string $key, \WP_Ultimo\Dependencies\Rakit\Validation\Rule $rule)
    {
        $this->validators[$key] = $rule;
        $rule->setKey($key);
    }
    /**
     * Get validator object from given $key
     *
     * @param mixed $key
     * @return mixed
     */
    public function getValidator($key)
    {
        return isset($this->validators[$key]) ? $this->validators[$key] : null;
    }
    /**
     * Validate $inputs
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return Validation
     */
    public function validate(array $inputs, array $rules, array $messages = []) : \WP_Ultimo\Dependencies\Rakit\Validation\Validation
    {
        $validation = $this->make($inputs, $rules, $messages);
        $validation->validate();
        return $validation;
    }
    /**
     * Given $inputs, $rules and $messages to make the Validation class instance
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return Validation
     */
    public function make(array $inputs, array $rules, array $messages = []) : \WP_Ultimo\Dependencies\Rakit\Validation\Validation
    {
        $messages = \array_merge($this->messages, $messages);
        $validation = new \WP_Ultimo\Dependencies\Rakit\Validation\Validation($this, $inputs, $rules, $messages);
        $validation->setTranslations($this->getTranslations());
        return $validation;
    }
    /**
     * Magic invoke method to make Rule instance
     *
     * @param string $rule
     * @return Rule
     * @throws RuleNotFoundException
     */
    public function __invoke(string $rule) : \WP_Ultimo\Dependencies\Rakit\Validation\Rule
    {
        $args = \func_get_args();
        $rule = \array_shift($args);
        $params = $args;
        $validator = $this->getValidator($rule);
        if (!$validator) {
            throw new \WP_Ultimo\Dependencies\Rakit\Validation\RuleNotFoundException("Validator '{$rule}' is not registered", 1);
        }
        $clonedValidator = clone $validator;
        $clonedValidator->fillParameters($params);
        return $clonedValidator;
    }
    /**
     * Initialize base validators array
     *
     * @return void
     */
    protected function registerBaseValidators()
    {
        $baseValidator = [
            'required' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Required(),
            'required_if' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\RequiredIf(),
            'required_unless' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\RequiredUnless(),
            'required_with' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\RequiredWith(),
            'required_without' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\RequiredWithout(),
            'required_with_all' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\RequiredWithAll(),
            'required_without_all' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\RequiredWithoutAll(),
            'email' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Email(),
            'alpha' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Alpha(),
            'numeric' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Numeric(),
            'alpha_num' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\AlphaNum(),
            'alpha_dash' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\AlphaDash(),
            'alpha_spaces' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\AlphaSpaces(),
            'in' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\In(),
            'not_in' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\NotIn(),
            'min' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Min(),
            'max' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Max(),
            'between' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Between(),
            'url' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Url(),
            'integer' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Integer(),
            'boolean' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Boolean(),
            'ip' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Ip(),
            'ipv4' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Ipv4(),
            'ipv6' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Ipv6(),
            'extension' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Extension(),
            'array' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\TypeArray(),
            'same' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Same(),
            'regex' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Regex(),
            'date' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Date(),
            'accepted' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Accepted(),
            'present' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Present(),
            'different' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Different(),
            'uploaded_file' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\UploadedFile(),
            'mimes' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Mimes(),
            'callback' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Callback(),
            'before' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Before(),
            'after' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\After(),
            'lowercase' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Lowercase(),
            'uppercase' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Uppercase(),
            'json' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Json(),
            'digits' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Digits(),
            'digits_between' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\DigitsBetween(),
            'defaults' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Defaults(),
            'default' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Defaults(),
            // alias of defaults
            'nullable' => new \WP_Ultimo\Dependencies\Rakit\Validation\Rules\Nullable(),
        ];
        foreach ($baseValidator as $key => $validator) {
            $this->setValidator($key, $validator);
        }
    }
    /**
     * Given $ruleName and $rule to add new validator
     *
     * @param string $ruleName
     * @param \Rakit\Validation\Rule $rule
     * @return void
     */
    public function addValidator(string $ruleName, \WP_Ultimo\Dependencies\Rakit\Validation\Rule $rule)
    {
        if (!$this->allowRuleOverride && \array_key_exists($ruleName, $this->validators)) {
            throw new \WP_Ultimo\Dependencies\Rakit\Validation\RuleQuashException("You cannot override a built in rule. You have to rename your rule");
        }
        $this->setValidator($ruleName, $rule);
    }
    /**
     * Set rule can allow to be overrided
     *
     * @param boolean $status
     * @return void
     */
    public function allowRuleOverride(bool $status = \false)
    {
        $this->allowRuleOverride = $status;
    }
    /**
     * Set this can use humanize keys
     *
     * @param boolean $useHumanizedKeys
     * @return void
     */
    public function setUseHumanizedKeys(bool $useHumanizedKeys = \true)
    {
        $this->useHumanizedKeys = $useHumanizedKeys;
    }
    /**
     * Get $this->useHumanizedKeys value
     *
     * @return void
     */
    public function isUsingHumanizedKey() : bool
    {
        return $this->useHumanizedKeys;
    }
}
