<?php

namespace Codeages\Biz\Framework\Validation;

/**
 * @deprecated
 */
class SimpleValidator implements Validator
{
    protected $data = array();

    protected $rules = array();

    protected $errors = array();

    protected $ruleMessages = array(
        'required' => '{key} is required',
        'string' => '{key} must be string',
        'numeric' => '{key} must be numeric',
        'integer' => '{key} must be an integer (0-9)',
        'float' => '{key} must be float',
        'boolean' => '{key} must be boolean',
        'array' => '{key} must be array',
        'alpha' => '{key} must be alphabetic characters',
        'alpha_num' => '{key} must be alpha-numeric characters',
        'alpha_dash' => '{key} must be alpha-numeric characters, dashes and underscores',
        'digits' => '{key} can only contains digits, and length must be {0}',
        'digits_between' => '{key} can only contains digits, and length must be between {0}-{1}',
        'min' => '{key} must be equal or greater than {0}',
        'max' => '{key} must be equal or less than {0}',
        'between' => '{key} must be between {0}-{1}',
        'length' => '{key} must be string, and length must be equal {0}',
        'length_min' => '{key} must be string, and length must be equal or greater than {0}',
        'length_max' => '{key} must be string, and length must be equal or less than {0}',
        'length_between' => '{key} must be string, and length must be between {0}-{1}',
        'email' => '{key} is not a valid email address',
        'in' => '{key} contains invalid value',
        'ip' => '{key} is not a valid IP address',
        'url' => '{key} must be url',
        'http_url' => '{key} must be http url',
        'date' => '{key} must be a valid date',
        'date_after' => '{key} must be a value after a given date',
        'date_after_or_equal' => '{key} must be a value after or equal to the given date',
        'date_before' => '{key} must be a value before a given date',
        'date_before_or_equal' => '{key} must be a value before or equal to the given date',
    );

    public function validate($fields, $fieldRules, $throwException = true)
    {
        $fields = array_intersect_key($fields, array_flip(array_keys($fieldRules)));

        foreach ($fieldRules as $key => $rules) {
            $rules = RuleParser::parse($rules);
            foreach ($rules as $rule) {
                if (is_string($rule)) {
                    $ruleName = $rule;
                    $params = array();
                } else {
                    $ruleName = array_shift($rule);
                    $params = $rule;
                }

                if (!isset($fields[$key]) || is_null($fields[$key]) || (is_string($fields[$key]) && '' == $fields[$key])) {
                    if ($this->inRules('required', $rules)) {
                        $this->addError($key, 'required', $params);
                    }
                    break;
                }

                if (isset($this->rules[$ruleName])) {
                    $func = $this->rules[$ruleName];
                    $isPass = $func($key, $fields[$key], $params);
                } else {
                    $method = 'validate'.str_replace(' ', '', ucwords(str_replace('_', ' ', $ruleName)));
                    $isPass = call_user_func_array(array($this, $method), array($key, $fields[$key], $params));
                }

                if (!$isPass) {
                    $this->addError($key, $ruleName, $params);
                }
            }
        }

        if ($this->errors && $throwException) {
            throw new ValidationException($this->errors);
        }

        if ($this->errors) {
            return null;
        }

        return $fields;
    }

    public function rule($name, $callback, $message = null)
    {
        $this->rules[$name] = $callback;
        $this->ruleMessages[$name] = $message;

        return $this;
    }

    public function extend(RuleExtension $extension)
    {
        $this->rules = array_merge($this->rules, $extension->rules());
        $this->ruleMessages = array_merge($this->rules, $extension->messages());
    }

    public function errors()
    {
        return $this->errors;
    }

    protected function inRules($ruleName, $rules)
    {
        foreach ($rules as $rule) {
            if (is_array($rule)) {
                $rule = array_shift($rule);
            }
            if ($ruleName == $rule) {
                return true;
            }
        }

        return false;
    }

    protected function addError($key, $ruleName, $params = array())
    {
        if (!isset($this->errors[$key])) {
            $this->errors[$key] = array();
        }

        $this->errors[$key][] = $this->formatErrorMessage($key, $ruleName, $params);
    }

    protected function formatErrorMessage($key, $ruleName, $params = array())
    {
        $message = $this->ruleMessages[$ruleName];
        $message = str_replace('{key}', $key, $message);

        foreach ($params as $index => $param) {
            $message = str_replace('{'.$index.'}', $param, $message);
        }

        return $message;
    }

    /**
     * 校验必填
     *
     * @param $field
     * @param $value
     *
     * @return bool
     */
    protected function validateRequired($field, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && '' === trim($value)) {
            return false;
        }

        return true;
    }

    /**
     * 校验字符串型
     *
     * @param $field
     * @param $value
     *
     * @return bool
     */
    protected function validateString($field, $value)
    {
        return is_string($value);
    }

    /**
     * 校验数字型
     *
     * @param $field
     * @param $value
     *
     * @return bool
     */
    protected function validateNumeric($field, $value)
    {
        return is_numeric($value);
    }

    /**
     * 校验整形
     *
     * @param $field
     * @param $value
     * @param $params
     *
     * @return bool
     */
    protected function validateInteger($field, $value, $params)
    {
        return false !== filter_var($value, FILTER_VALIDATE_INT);
    }

    protected function validateFloat($field, $value, $params)
    {
        $isFloat = false !== filter_var($value, FILTER_VALIDATE_FLOAT);
        if (!$isFloat) {
            return false;
        }

        if (!isset($params[0])) {
            return true;
        }

        $value = (string) $value;

        $dotPos = strpos($value, '.');
        if (false === $dotPos) {
            return true;
        }

        return strlen($value) - ($dotPos + 1) <= $params[0];
    }

    protected function validateBoolean($field, $value)
    {
        $acceptable = array(true, false, 0, 1, '0', '1');

        return in_array($value, $acceptable, true);
    }

    protected function validateArray($field, $value)
    {
        return is_array($value);
    }

    /**
     * 校验字母
     *
     * @param $field
     * @param $value
     *
     * @return bool
     */
    protected function validateAlpha($field, $value)
    {
        return is_string($value) && preg_match('/^([a-z])+$/i', $value);
    }

    protected function validateAlphaNum($field, $value)
    {
        return is_string($value) && preg_match('/^([a-z0-9])+$/i', $value);
    }

    /**
     * 校验
     *
     * @param $field
     * @param $value
     *
     * @return bool
     */
    protected function validateAlphaDash($field, $value)
    {
        return is_string($value) && preg_match('/^([-a-z0-9_-])+$/i', $value);
    }

    /**
     * 校验数字及长度
     *
     * @param $field
     * @param $value
     * @param $params
     *
     * @return bool
     */
    protected function validateDigits($field, $value, $params)
    {
        return !preg_match('/[^0-9]/', $value) && strlen((string) $value) == $params[0];
    }

    /**
     * 校验数字及长度的范围
     *
     * @param $field
     * @param $value
     * @param $params
     *
     * @return bool
     */
    protected function validateDigitsBetween($field, $value, $params)
    {
        $length = strlen((string) $value);

        return !preg_match('/[^0-9]/', $value) && $length >= $params[0] && $length <= $params[1];
    }

    protected function validateMin($field, $value, $params)
    {
        return is_numeric($value) && ($params[0] <= $value);
    }

    protected function validateMax($field, $value, $params)
    {
        return is_numeric($value) && ($params[0] >= $value);
    }

    protected function validateBetween($field, $value, $params)
    {
        return $this->validateMin($field, $value, array($params[0])) && $this->validateMax($field, $value, array($params[1]));
    }

    protected function validateLength($field, $value, $params)
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);

        return $length == $params[0];
    }

    protected function validateLengthMin($field, $value, $params)
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);

        return $length >= $params[0];
    }

    protected function validateLengthMax($field, $value, $params)
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);

        return $length <= $params[0];
    }

    protected function validateLengthBetween($field, $value, $params)
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);

        return $length >= $params[0] && $length <= $params[1];
    }

    protected function validateEmail($field, $value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected function validateIp($field, $value)
    {
        return false !== filter_var($value, \FILTER_VALIDATE_IP);
    }

    protected function validateUrl($field, $value)
    {
        return false !== filter_var($value, \FILTER_VALIDATE_URL);
    }

    protected function validateHttpUrl($field, $value)
    {
        if (0 !== strpos($value, 'http://') && 0 !== strpos($value, 'https://')) {
            return false;
        }

        return $this->validateUrl($field, $value);
    }

    protected function validateIn($field, $value, $params)
    {
        return in_array($value, $params);
    }

    protected function validateDate($field, $value)
    {
        if ($value instanceof \DateTime) {
            return true;
        }

        if ((!is_string($value) && !is_numeric($value)) || false === strtotime($value)) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    protected function validateDateFormat($field, $value, $params)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }

        $date = \DateTime::createFromFormat($params[0], $value);

        return $date && $date->format($params[0]) == $value;
    }

    protected function validateDateAfter($field, $value, $params)
    {
        $vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $ptime = ($params[0] instanceof \DateTime) ? $params[0]->getTimestamp() : strtotime($params[0]);

        return $vtime > $ptime;
    }

    protected function validateDateAfterOrEqual($field, $value, $params)
    {
        $vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $ptime = ($params[0] instanceof \DateTime) ? $params[0]->getTimestamp() : strtotime($params[0]);

        return $vtime >= $ptime;
    }

    protected function validateDateBefore($field, $value, $params)
    {
        $vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $ptime = ($params[0] instanceof \DateTime) ? $params[0]->getTimestamp() : strtotime($params[0]);

        return $vtime < $ptime;
    }

    protected function validateDateBeforeOrEqual($field, $value, $params)
    {
        $vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $ptime = ($params[0] instanceof \DateTime) ? $params[0]->getTimestamp() : strtotime($params[0]);

        return $vtime <= $ptime;
    }
}
