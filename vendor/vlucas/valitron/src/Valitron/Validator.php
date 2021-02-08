<?php

namespace Valitron;

/**
 * Validation Class
 *
 * Validates input against certain criteria
 *
 * @package Valitron
 * @author  Vance Lucas <vance@vancelucas.com>
 * @link    http://www.vancelucas.com/
 */
class Validator
{
    /**
     * @var string
     */
    const ERROR_DEFAULT = 'Invalid';

    /**
     * @var array
     */
    protected $_fields = array();

    /**
     * @var array
     */
    protected $_errors = array();

    /**
     * @var array
     */
    protected $_validations = array();

    /**
     * @var array
     */
    protected $_labels = array();

    /**
     * Contains all rules that are available to the current valitron instance.
     *
     * @var array
     */
    protected $_instanceRules = array();

    /**
     * Contains all rule messages that are available to the current valitron
     * instance
     *
     * @var array
     */
    protected $_instanceRuleMessage = array();

    /**
     * @var string
     */
    protected static $_lang;

    /**
     * @var string
     */
    protected static $_langDir;

    /**
     * @var array
     */
    protected static $_rules = array();

    /**
     * @var array
     */
    protected static $_ruleMessages = array();

    /**
     * @var array
     */
    protected $validUrlPrefixes = array('http://', 'https://', 'ftp://');

    /**
     * @var bool
     */
    protected $stop_on_first_fail = false;

    /**
     * @var bool
     */
    protected $prepend_labels = true;

    /**
     * Setup validation
     *
     * @param  array $data
     * @param  array $fields
     * @param  string $lang
     * @param  string $langDir
     * @throws \InvalidArgumentException
     */
    public function __construct($data = array(), $fields = array(), $lang = null, $langDir = null)
    {
        // Allows filtering of used input fields against optional second array of field names allowed
        // This is useful for limiting raw $_POST or $_GET data to only known fields
        $this->_fields = !empty($fields) ? array_intersect_key($data, array_flip($fields)) : $data;

        // set lang in the follow order: constructor param, static::$_lang, default to en
        $lang = $lang ?: static::lang();

        // set langDir in the follow order: constructor param, static::$_langDir, default to package lang dir
        $langDir = $langDir ?: static::langDir();

        // Load language file in directory
        $langFile = rtrim($langDir, '/') . '/' . $lang . '.php';
        if (stream_resolve_include_path($langFile)) {
            $langMessages = include $langFile;
            static::$_ruleMessages = array_merge(static::$_ruleMessages, $langMessages);
        } else {
            throw new \InvalidArgumentException("Fail to load language file '" . $langFile . "'");
        }
    }

    /**
     * Get/set language to use for validation messages
     *
     * @param  string $lang
     * @return string
     */
    public static function lang($lang = null)
    {
        if ($lang !== null) {
            static::$_lang = $lang;
        }

        return static::$_lang ?: 'en';
    }

    /**
     * Get/set language file path
     *
     * @param  string $dir
     * @return string
     */
    public static function langDir($dir = null)
    {
        if ($dir !== null) {
            static::$_langDir = $dir;
        }

        return static::$_langDir ?: dirname(dirname(__DIR__)) . '/lang';
    }

    /**
     * @param bool $prepend_labels
     */
    public function setPrependLabels($prepend_labels = true)
    {
        $this->prepend_labels = $prepend_labels;
    }

    /**
     * Required field validator
     *
     * @param  string $field
     * @param  mixed $value
     * @param  array $params
     * @return bool
     */
    protected function validateRequired($field, $value, $params = array())
    {
        if (isset($params[0]) && (bool)$params[0]) {
            $find = $this->getPart($this->_fields, explode('.', $field), true);
            return $find[1];
        }

        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }

    /**
     * Validate that two values match
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateEquals($field, $value, array $params)
    {
        // extract the second field value, this accounts for nested array values
        list($field2Value, $multiple) = $this->getPart($this->_fields, explode('.', $params[0]));
        return isset($field2Value) && $value == $field2Value;
    }

    /**
     * Validate that a field is different from another field
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateDifferent($field, $value, array $params)
    {
        // extract the second field value, this accounts for nested array values
        list($field2Value, $multiple) = $this->getPart($this->_fields, explode('.', $params[0]));
        return isset($field2Value) && $value != $field2Value;
    }

    /**
     * Validate that a field was "accepted" (based on PHP's string evaluation rules)
     *
     * This validation rule implies the field is "required"
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateAccepted($field, $value)
    {
        $acceptable = array('yes', 'on', 1, '1', true);

        return $this->validateRequired($field, $value) && in_array($value, $acceptable, true);
    }

    /**
     * Validate that a field is an array
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateArray($field, $value)
    {
        return is_array($value);
    }

    /**
     * Validate that a field is numeric
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateNumeric($field, $value)
    {
        return is_numeric($value);
    }

    /**
     * Validate that a field is an integer
     *
     * @param  string $field
     * @param  mixed $value
     * @param  array $params
     * @return bool
     */
    protected function validateInteger($field, $value, $params)
    {
        if (isset($params[0]) && (bool)$params[0]) {
            //strict mode
            return preg_match('/^([0-9]|-[1-9]|-?[1-9][0-9]*)$/i', $value);
        }

        return filter_var($value, \FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate the length of a string
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateLength($field, $value, $params)
    {
        $length = $this->stringLength($value);
        // Length between
        if (isset($params[1])) {
            return $length >= $params[0] && $length <= $params[1];
        }
        // Length same
        return ($length !== false) && $length == $params[0];
    }

    /**
     * Validate the length of a string (between)
     *
     * @param  string  $field
     * @param  mixed   $value
     * @param  array   $params
     * @return bool
     */
    protected function validateLengthBetween($field, $value, $params)
    {
        $length = $this->stringLength($value);

        return ($length !== false) && $length >= $params[0] && $length <= $params[1];
    }

    /**
     * Validate the length of a string (min)
     *
     * @param string $field
     * @param mixed $value
     * @param array $params
     *
     * @return bool
     */
    protected function validateLengthMin($field, $value, $params)
    {
        $length = $this->stringLength($value);

        return ($length !== false) && $length >= $params[0];
    }

    /**
     * Validate the length of a string (max)
     *
     * @param string $field
     * @param mixed $value
     * @param array $params
     *
     * @return bool
     */
    protected function validateLengthMax($field, $value, $params)
    {
        $length = $this->stringLength($value);

        return ($length !== false) && $length <= $params[0];
    }

    /**
     * Get the length of a string
     *
     * @param  string $value
     * @return int|false
     */
    protected function stringLength($value)
    {
        if (!is_string($value)) {
            return false;
        } elseif (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }

    /**
     * Validate the size of a field is greater than a minimum value.
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateMin($field, $value, $params)
    {
        if (!is_numeric($value)) {
            return false;
        } elseif (function_exists('bccomp')) {
            return !(bccomp($params[0], $value, 14) === 1);
        } else {
            return $params[0] <= $value;
        }
    }

    /**
     * Validate the size of a field is less than a maximum value
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateMax($field, $value, $params)
    {
        if (!is_numeric($value)) {
            return false;
        } elseif (function_exists('bccomp')) {
            return !(bccomp($value, $params[0], 14) === 1);
        } else {
            return $params[0] >= $value;
        }
    }

    /**
     * Validate the size of a field is between min and max values
     *
     * @param  string $field
     * @param  mixed $value
     * @param  array $params
     * @return bool
     */
    protected function validateBetween($field, $value, $params)
    {
        if (!is_numeric($value)) {
            return false;
        }
        if (!isset($params[0]) || !is_array($params[0]) || count($params[0]) !== 2) {
            return false;
        }

        list($min, $max) = $params[0];

        return $this->validateMin($field, $value, array($min)) && $this->validateMax($field, $value, array($max));
    }

    /**
     * Validate a field is contained within a list of values
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateIn($field, $value, $params)
    {
        $isAssoc = array_values($params[0]) !== $params[0];
        if ($isAssoc) {
            $params[0] = array_keys($params[0]);
        }

        $strict = false;
        if (isset($params[1])) {
            $strict = $params[1];
        }

        return in_array($value, $params[0], $strict);
    }

    /**
     * Validate a field is contained within a list of values
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateListContains($field, $value, $params)
    {
        $isAssoc = array_values($value) !== $value;
        if ($isAssoc) {
            $value = array_keys($value);
        }

        $strict = false;
        if (isset($params[1])) {
            $strict = $params[1];
        }

        return in_array($params[0], $value, $strict);
    }

    /**
     * Validate a field is not contained within a list of values
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateNotIn($field, $value, $params)
    {
        return !$this->validateIn($field, $value, $params);
    }

    /**
     * Validate a field contains a given string
     *
     * @param  string $field
     * @param  string $value
     * @param  array  $params
     * @return bool
     */
    protected function validateContains($field, $value, $params)
    {
        if (!isset($params[0])) {
            return false;
        }
        if (!is_string($params[0]) || !is_string($value)) {
            return false;
        }

        $strict = true;
        if (isset($params[1])) {
            $strict = (bool)$params[1];
        }

        if ($strict) {
            if (function_exists('mb_strpos')) {
                $isContains = mb_strpos($value, $params[0]) !== false;
            } else {
                $isContains = strpos($value, $params[0]) !== false;
            }
        } else {
            if (function_exists('mb_stripos')) {
                $isContains = mb_stripos($value, $params[0]) !== false;
            } else {
                $isContains = stripos($value, $params[0]) !== false;
            }
        }
        return $isContains;
    }

    /**
     * Validate that all field values contains a given array
     *
     * @param  string $field
     * @param  array  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateSubset($field, $value, $params)
    {
        if (!isset($params[0])) {
            return false;
        }
        if (!is_array($params[0])) {
            $params[0] = array($params[0]);
        }
        if (is_scalar($value) || is_null($value)) {
            return $this->validateIn($field, $value, $params);
        }

        $intersect = array_intersect($value, $params[0]);
        return array_diff($value, $intersect) === array_diff($intersect, $value);
    }

    /**
     * Validate that field array has only unique values
     *
     * @param  string $field
     * @param  array  $value
     * @return bool
     */
    protected function validateContainsUnique($field, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        return $value === array_unique($value, SORT_REGULAR);
    }

    /**
     * Validate that a field is a valid IP address
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateIp($field, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that a field is a valid IP v4 address
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateIpv4($field, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Validate that a field is a valid IP v6 address
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateIpv6($field, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Validate that a field is a valid e-mail address
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateEmail($field, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate that a field contains only ASCII characters
     *
     * @param $field
     * @param $value
     * @return bool|false|string
     */
    protected function validateAscii($field, $value)
    {
        // multibyte extension needed
        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($value, 'ASCII', true);
        }

        // fallback with regex
        return 0 === preg_match('/[^\x00-\x7F]/', $value);
    }

    /**
     * Validate that a field is a valid e-mail address and the domain name is active
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateEmailDNS($field, $value)
    {
        if ($this->validateEmail($field, $value)) {
            $domain = ltrim(stristr($value, '@'), '@') . '.';
            if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46')) {
                $domain = idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
            }
            return checkdnsrr($domain, 'ANY');
        }

        return false;
    }

    /**
     * Validate that a field is a valid URL by syntax
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateUrl($field, $value)
    {
        foreach ($this->validUrlPrefixes as $prefix) {
            if (strpos($value, $prefix) !== false) {
                return filter_var($value, \FILTER_VALIDATE_URL) !== false;
            }
        }

        return false;
    }

    /**
     * Validate that a field is an active URL by verifying DNS record
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateUrlActive($field, $value)
    {
        foreach ($this->validUrlPrefixes as $prefix) {
            if (strpos($value, $prefix) !== false) {
                $host = parse_url(strtolower($value), PHP_URL_HOST);

                return checkdnsrr($host, 'A') || checkdnsrr($host, 'AAAA') || checkdnsrr($host, 'CNAME');
            }
        }

        return false;
    }

    /**
     * Validate that a field contains only alphabetic characters
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateAlpha($field, $value)
    {
        return preg_match('/^([a-z])+$/i', $value);
    }

    /**
     * Validate that a field contains only alpha-numeric characters
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateAlphaNum($field, $value)
    {
        return preg_match('/^([a-z0-9])+$/i', $value);
    }

    /**
     * Validate that a field contains only alpha-numeric characters, dashes, and underscores
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateSlug($field, $value)
    {
        if (is_array($value)) {
            return false;
        }
        return preg_match('/^([-a-z0-9_-])+$/i', $value);
    }

    /**
     * Validate that a field passes a regular expression check
     *
     * @param  string $field
     * @param  mixed $value
     * @param  array $params
     * @return bool
     */
    protected function validateRegex($field, $value, $params)
    {
        return preg_match($params[0], $value);
    }

    /**
     * Validate that a field is a valid date
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateDate($field, $value)
    {
        $isDate = false;
        if ($value instanceof \DateTime) {
            $isDate = true;
        } else {
            $isDate = strtotime($value) !== false;
        }

        return $isDate;
    }

    /**
     * Validate that a field matches a date format
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateDateFormat($field, $value, $params)
    {
        $parsed = date_parse_from_format($params[0], $value);

        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * Validate the date is before a given date
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateDateBefore($field, $value, $params)
    {
        $vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $ptime = ($params[0] instanceof \DateTime) ? $params[0]->getTimestamp() : strtotime($params[0]);

        return $vtime < $ptime;
    }

    /**
     * Validate the date is after a given date
     *
     * @param  string $field
     * @param  mixed  $value
     * @param  array  $params
     * @return bool
     */
    protected function validateDateAfter($field, $value, $params)
    {
        $vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
        $ptime = ($params[0] instanceof \DateTime) ? $params[0]->getTimestamp() : strtotime($params[0]);

        return $vtime > $ptime;
    }

    /**
     * Validate that a field contains a boolean.
     *
     * @param  string $field
     * @param  mixed $value
     * @return bool
     */
    protected function validateBoolean($field, $value)
    {
        return is_bool($value);
    }

    /**
     * Validate that a field contains a valid credit card
     * optionally filtered by an array
     *
     * @param  string $field
     * @param  mixed $value
     * @param  array $params
     * @return bool
     */
    protected function validateCreditCard($field, $value, $params)
    {
        /**
         * I there has been an array of valid cards supplied, or the name of the users card
         * or the name and an array of valid cards
         */
        if (!empty($params)) {
            /**
             * array of valid cards
             */
            if (is_array($params[0])) {
                $cards = $params[0];
            } elseif (is_string($params[0])) {
                $cardType = $params[0];
                if (isset($params[1]) && is_array($params[1])) {
                    $cards = $params[1];
                    if (!in_array($cardType, $cards)) {
                        return false;
                    }
                }
            }
        }
        /**
         * Luhn algorithm
         *
         * @return bool
         */
        $numberIsValid = function () use ($value) {
            $number = preg_replace('/[^0-9]+/', '', $value);
            $sum = 0;

            $strlen = strlen($number);
            if ($strlen < 13) {
                return false;
            }
            for ($i = 0; $i < $strlen; $i++) {
                $digit = (int)substr($number, $strlen - $i - 1, 1);
                if ($i % 2 == 1) {
                    $sub_total = $digit * 2;
                    if ($sub_total > 9) {
                        $sub_total = ($sub_total - 10) + 1;
                    }
                } else {
                    $sub_total = $digit;
                }
                $sum += $sub_total;
            }
            if ($sum > 0 && $sum % 10 == 0) {
                return true;
            }

            return false;
        };

        if ($numberIsValid()) {
            if (!isset($cards)) {
                return true;
            } else {
                $cardRegex = array(
                    'visa' => '#^4[0-9]{12}(?:[0-9]{3})?$#',
                    'mastercard' => '#^(5[1-5]|2[2-7])[0-9]{14}$#',
                    'amex' => '#^3[47][0-9]{13}$#',
                    'dinersclub' => '#^3(?:0[0-5]|[68][0-9])[0-9]{11}$#',
                    'discover' => '#^6(?:011|5[0-9]{2})[0-9]{12}$#',
                );

                if (isset($cardType)) {
                    // if we don't have any valid cards specified and the card we've been given isn't in our regex array
                    if (!isset($cards) && !in_array($cardType, array_keys($cardRegex))) {
                        return false;
                    }

                    // we only need to test against one card type
                    return (preg_match($cardRegex[$cardType], $value) === 1);

                } elseif (isset($cards)) {
                    // if we have cards, check our users card against only the ones we have
                    foreach ($cards as $card) {
                        if (in_array($card, array_keys($cardRegex))) {
                            // if the card is valid, we want to stop looping
                            if (preg_match($cardRegex[$card], $value) === 1) {
                                return true;
                            }
                        }
                    }
                } else {
                    // loop through every card
                    foreach ($cardRegex as $regex) {
                        // until we find a valid one
                        if (preg_match($regex, $value) === 1) {
                            return true;
                        }
                    }
                }
            }
        }

        // if we've got this far, the card has passed no validation so it's invalid!
        return false;
    }

    protected function validateInstanceOf($field, $value, $params)
    {
        $isInstanceOf = false;
        if (is_object($value)) {
            if (is_object($params[0]) && $value instanceof $params[0]) {
                $isInstanceOf = true;
            }
            if (get_class($value) === $params[0]) {
                $isInstanceOf = true;
            }
        }
        if (is_string($value)) {
            if (is_string($params[0]) && get_class($value) === $params[0]) {
                $isInstanceOf = true;
            }
        }

        return $isInstanceOf;
    }

    /**
     * Validates whether or not a field is required based on whether or not other fields are present.
     *
     * @param string $field name of the field in the data array
     * @param mixed $value value of this field
     * @param array $params parameters for this rule
     * @param array $fields full list of data to be validated
     * @return bool
     */
    protected function validateRequiredWith($field, $value, $params, $fields)
    {
        $conditionallyReq = false;
        // if we actually have conditionally required with fields to check against
        if (isset($params[0])) {
            // convert single value to array if it isn't already
            $reqParams = is_array($params[0]) ? $params[0] : array($params[0]);
            // check for the flag indicating if all fields are required
            $allRequired = isset($params[1]) && (bool)$params[1];
            $emptyFields = 0;
            foreach ($reqParams as $requiredField) {
                // check the field is set, not null, and not the empty string
                if (isset($fields[$requiredField]) && !is_null($fields[$requiredField])
                    && (is_string($fields[$requiredField]) ? trim($fields[$requiredField]) !== '' : true)) {
                    if (!$allRequired) {
                        $conditionallyReq = true;
                        break;
                    } else {
                        $emptyFields++;
                    }
                }
            }
            // if all required fields are present in strict mode, we're requiring it
            if ($allRequired && $emptyFields === count($reqParams)) {
                $conditionallyReq = true;
            }
        }
        // if we have conditionally required fields
        if ($conditionallyReq && (is_null($value) ||
                is_string($value) && trim($value) === '')) {
            return false;
        }
        return true;
    }

    /**
     * Validates whether or not a field is required based on whether or not other fields are present.
     *
     * @param string $field name of the field in the data array
     * @param mixed $value value of this field
     * @param array $params parameters for this rule
     * @param array $fields full list of data to be validated
     * @return bool
     */
    protected function validateRequiredWithout($field, $value, $params, $fields)
    {
        $conditionallyReq = false;
        // if we actually have conditionally required with fields to check against
        if (isset($params[0])) {
            // convert single value to array if it isn't already
            $reqParams = is_array($params[0]) ? $params[0] : array($params[0]);
            // check for the flag indicating if all fields are required
            $allEmpty = isset($params[1]) && (bool)$params[1];
            $filledFields = 0;
            foreach ($reqParams as $requiredField) {
                // check the field is NOT set, null, or the empty string, in which case we are requiring this value be present
                if (!isset($fields[$requiredField]) || (is_null($fields[$requiredField])
                    || (is_string($fields[$requiredField]) && trim($fields[$requiredField]) === ''))) {
                    if (!$allEmpty) {
                        $conditionallyReq = true;
                        break;
                    } else {
                        $filledFields++;
                    }
                }
            }
            // if all fields were empty, then we're requiring this in strict mode
            if ($allEmpty && $filledFields === count($reqParams)) {
                $conditionallyReq = true;
            }
        }
        // if we have conditionally required fields
        if ($conditionallyReq && (is_null($value) ||
                is_string($value) && trim($value) === '')) {
            return false;
        }
        return true;
    }

    /**
     * Validate optional field
     *
     * @param $field
     * @param $value
     * @param $params
     * @return bool
     */
    protected function validateOptional($field, $value, $params)
    {
        //Always return true
        return true;
    }

    protected function validateArrayHasKeys($field, $value, $params)
    {
        if (!is_array($value) || !isset($params[0])) {
            return false;
        }
        $requiredFields = $params[0];
        if (count($requiredFields) === 0) {
            return false;
        }
        foreach ($requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get array of fields and data
     *
     * @return array
     */
    public function data()
    {
        return $this->_fields;
    }

    /**
     * Get array of error messages
     *
     * @param  null|string $field
     * @return array|bool
     */
    public function errors($field = null)
    {
        if ($field !== null) {
            return isset($this->_errors[$field]) ? $this->_errors[$field] : false;
        }

        return $this->_errors;
    }

    /**
     * Add an error to error messages array
     *
     * @param string $field
     * @param string $message
     * @param array  $params
     */
    public function error($field, $message, array $params = array())
    {
        $message = $this->checkAndSetLabel($field, $message, $params);

        $values = array();
        // Printed values need to be in string format
        foreach ($params as $param) {
            if (is_array($param)) {
                $param = "['" . implode("', '", $param) . "']";
            }
            if ($param instanceof \DateTime) {
                $param = $param->format('Y-m-d');
            } else {
                if (is_object($param)) {
                    $param = get_class($param);
                }
            }
            // Use custom label instead of field name if set
            if (is_string($params[0])) {
                if (isset($this->_labels[$param])) {
                    $param = $this->_labels[$param];
                }
            }
            $values[] = $param;
        }

        $this->_errors[$field][] = vsprintf($message, $values);
    }

    /**
     * Specify validation message to use for error for the last validation rule
     *
     * @param  string $message
     * @return Validator
     */
    public function message($message)
    {
        $this->_validations[count($this->_validations) - 1]['message'] = $message;

        return $this;
    }

    /**
     * Reset object properties
     */
    public function reset()
    {
        $this->_fields = array();
        $this->_errors = array();
        $this->_validations = array();
        $this->_labels = array();
    }

    protected function getPart($data, $identifiers, $allow_empty = false)
    {
        // Catches the case where the field is an array of discrete values
        if (is_array($identifiers) && count($identifiers) === 0) {
            return array($data, false);
        }
        // Catches the case where the data isn't an array or object
        if (is_scalar($data)) {
            return array(null, false);
        }
        $identifier = array_shift($identifiers);
        // Glob match
        if ($identifier === '*') {
            $values = array();
            foreach ($data as $row) {
                list($value, $multiple) = $this->getPart($row, $identifiers, $allow_empty);
                if ($multiple) {
                    $values = array_merge($values, $value);
                } else {
                    $values[] = $value;
                }
            }
            return array($values, true);
        } // Dead end, abort
        elseif ($identifier === null || ! isset($data[$identifier])) {
            if ($allow_empty){
                //when empty values are allowed, we only care if the key exists
                return array(null, array_key_exists($identifier, $data));
            }
            return array(null, false);
        } // Match array element
        elseif (count($identifiers) === 0) {
            if ($allow_empty) {
                //when empty values are allowed, we only care if the key exists
                return array(null, array_key_exists($identifier, $data));
            }
            return array($data[$identifier], $allow_empty);
        } // We need to go deeper
        else {
            return $this->getPart($data[$identifier], $identifiers, $allow_empty);
        }
    }

    private function validationMustBeExcecuted($validation, $field, $values, $multiple){
        //always excecute requiredWith(out) rules
        if (in_array($validation['rule'], array('requiredWith', 'requiredWithout'))){
            return true;
        }

        //do not execute if the field is optional and not set
        if($this->hasRule('optional', $field) && ! isset($values)){
            return false;
        }

        //ignore empty input, except for required and accepted rule
        if (! $this->hasRule('required', $field) && ! in_array($validation['rule'], array('required', 'accepted'))){
            if($multiple){
                return count($values) != 0;
            }
            return (isset($values) && $values !== '');
        }

        return true;
    }
    /**
     * Run validations and return boolean result
     *
     * @return bool
     */
    public function validate()
    {
        $set_to_break = false;
        foreach ($this->_validations as $v) {
            foreach ($v['fields'] as $field) {
                list($values, $multiple) = $this->getPart($this->_fields, explode('.', $field), false);

                if (! $this->validationMustBeExcecuted($v, $field, $values, $multiple)){
                    continue;
                }

                // Callback is user-specified or assumed method on class
                $errors = $this->getRules();
                if (isset($errors[$v['rule']])) {
                    $callback = $errors[$v['rule']];
                } else {
                    $callback = array($this, 'validate' . ucfirst($v['rule']));
                }

                if (!$multiple) {
                    $values = array($values);
                } else if (! $this->hasRule('required', $field)){
                    $values = array_filter($values);
                }

                $result = true;
                foreach ($values as $value) {
                    $result = $result && call_user_func($callback, $field, $value, $v['params'], $this->_fields);
                }

                if (!$result) {
                    $this->error($field, $v['message'], $v['params']);
                    if ($this->stop_on_first_fail) {
                        $set_to_break = true;
                        break;
                    }
                }
            }
            if ($set_to_break) {
                break;
            }
        }

        return count($this->errors()) === 0;
    }

    /**
     * Should the validation stop a rule is failed
     * @param bool $stop
     */
    public function stopOnFirstFail($stop = true)
    {
        $this->stop_on_first_fail = (bool)$stop;
    }

    /**
     * Returns all rule callbacks, the static and instance ones.
     *
     * @return array
     */
    protected function getRules()
    {
        return array_merge($this->_instanceRules, static::$_rules);
    }

    /**
     * Returns all rule message, the static and instance ones.
     *
     * @return array
     */
    protected function getRuleMessages()
    {
        return array_merge($this->_instanceRuleMessage, static::$_ruleMessages);
    }

    /**
     * Determine whether a field is being validated by the given rule.
     *
     * @param  string  $name  The name of the rule
     * @param  string  $field The name of the field
     * @return bool
     */
    protected function hasRule($name, $field)
    {
        foreach ($this->_validations as $validation) {
            if ($validation['rule'] == $name) {
                if (in_array($field, $validation['fields'])) {
                    return true;
                }
            }
        }

        return false;
    }

    protected static function assertRuleCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(
                'Second argument must be a valid callback. Given argument was not callable.'
            );
        }
    }

    /**
     * Adds a new validation rule callback that is tied to the current
     * instance only.
     *
     * @param string $name
     * @param callable $callback
     * @param string $message
     * @throws \InvalidArgumentException
     */
    public function addInstanceRule($name, $callback, $message = null)
    {
        static::assertRuleCallback($callback);

        $this->_instanceRules[$name] = $callback;
        $this->_instanceRuleMessage[$name] = $message;
    }

    /**
     * Register new validation rule callback
     *
     * @param string $name
     * @param callable $callback
     * @param string $message
     * @throws \InvalidArgumentException
     */
    public static function addRule($name, $callback, $message = null)
    {
        if ($message === null) {
            $message = static::ERROR_DEFAULT;
        }

        static::assertRuleCallback($callback);

        static::$_rules[$name] = $callback;
        static::$_ruleMessages[$name] = $message;
    }

    /**
     * @param  mixed $fields
     * @return string
     */
    public function getUniqueRuleName($fields)
    {
        if (is_array($fields)) {
            $fields = implode("_", $fields);
        }

        $orgName = "{$fields}_rule";
        $name = $orgName;
        $rules = $this->getRules();
        while (isset($rules[$name])) {
            $name = $orgName . "_" . rand(0, 10000);
        }

        return $name;
    }

    /**
     * Returns true if either a validator with the given name has been
     * registered or there is a default validator by that name.
     *
     * @param string $name
     * @return bool
     */
    public function hasValidator($name)
    {
        $rules = $this->getRules();
        return method_exists($this, "validate" . ucfirst($name))
            || isset($rules[$name]);
    }

    /**
     * Convenience method to add a single validation rule
     *
     * @param string|callable $rule
     * @param array|string $fields
     * @return Validator
     * @throws \InvalidArgumentException
     */
    public function rule($rule, $fields)
    {
        // Get any other arguments passed to function
        $params = array_slice(func_get_args(), 2);

        if (is_callable($rule)
            && !(is_string($rule) && $this->hasValidator($rule))) {
            $name = $this->getUniqueRuleName($fields);
            $message = isset($params[0]) ? $params[0] : null;
            $this->addInstanceRule($name, $rule, $message);
            $rule = $name;
        }

        $errors = $this->getRules();
        if (!isset($errors[$rule])) {
            $ruleMethod = 'validate' . ucfirst($rule);
            if (!method_exists($this, $ruleMethod)) {
                throw new \InvalidArgumentException(
                    "Rule '" . $rule . "' has not been registered with " . get_called_class() . "::addRule()."
                );
            }
        }

        // Ensure rule has an accompanying message
        $messages = $this->getRuleMessages();
        $message = isset($messages[$rule]) ? $messages[$rule] : self::ERROR_DEFAULT;

        // Ensure message contains field label
        if (function_exists('mb_strpos')) {
            $notContains = mb_strpos($message, '{field}') === false;
        } else {
            $notContains = strpos($message, '{field}') === false;
        }
        if ($notContains) {
            $message = '{field} ' . $message;
        }

        $this->_validations[] = array(
            'rule' => $rule,
            'fields' => (array)$fields,
            'params' => (array)$params,
            'message' => $message
        );

        return $this;
    }

    /**
     * Add label to rule
     *
     * @param  string $value
     * @return Validator
     */
    public function label($value)
    {
        $lastRules = $this->_validations[count($this->_validations) - 1]['fields'];
        $this->labels(array($lastRules[0] => $value));

        return $this;
    }

    /**
     * Add labels to rules
     *
     * @param  array  $labels
     * @return Validator
     */
    public function labels($labels = array())
    {
        $this->_labels = array_merge($this->_labels, $labels);

        return $this;
    }

    /**
     * @param  string $field
     * @param  string $message
     * @param  array  $params
     * @return array
     */
    protected function checkAndSetLabel($field, $message, $params)
    {
        if (isset($this->_labels[$field])) {
            $message = str_replace('{field}', $this->_labels[$field], $message);

            if (is_array($params)) {
                $i = 1;
                foreach ($params as $k => $v) {
                    $tag = '{field' . $i . '}';
                    $label = isset($params[$k]) && (is_numeric($params[$k]) || is_string($params[$k])) && isset($this->_labels[$params[$k]]) ? $this->_labels[$params[$k]] : $tag;
                    $message = str_replace($tag, $label, $message);
                    $i++;
                }
            }
        } else {
            $message = $this->prepend_labels
                ? str_replace('{field}', ucwords(str_replace('_', ' ', $field)), $message)
                : str_replace('{field} ', '', $message);
        }

        return $message;
    }

    /**
     * Convenience method to add multiple validation rules with an array
     *
     * @param array $rules
     */
    public function rules($rules)
    {
        foreach ($rules as $ruleType => $params) {
            if (is_array($params)) {
                foreach ($params as $innerParams) {
                    if (!is_array($innerParams)) {
                        $innerParams = (array)$innerParams;
                    }
                    array_unshift($innerParams, $ruleType);
                    call_user_func_array(array($this, 'rule'), $innerParams);
                }
            } else {
                $this->rule($ruleType, $params);
            }
        }
    }

    /**
     * Replace data on cloned instance
     *
     * @param  array $data
     * @param  array $fields
     * @return Validator
     */
    public function withData($data, $fields = array())
    {
        $clone = clone $this;
        $clone->_fields = !empty($fields) ? array_intersect_key($data, array_flip($fields)) : $data;
        $clone->_errors = array();
        return $clone;
    }

    /**
     * Convenience method to add validation rule(s) by field
     *
     * @param string $field
     * @param array  $rules
     */
    public function mapFieldRules($field, $rules)
    {
        $me = $this;

        array_map(function ($rule) use ($field, $me) {

            //rule must be an array
            $rule = (array)$rule;

            //First element is the name of the rule
            $ruleName = array_shift($rule);

            //find a custom message, if any
            $message = null;
            if (isset($rule['message'])) {
                $message = $rule['message'];
                unset($rule['message']);
            }
            //Add the field and additional parameters to the rule
            $added = call_user_func_array(array($me, 'rule'), array_merge(array($ruleName, $field), $rule));
            if (!empty($message)) {
                $added->message($message);
            }
        }, (array)$rules);
    }

    /**
     * Convenience method to add validation rule(s) for multiple fields
     *
     * @param array $rules
     */
    public function mapFieldsRules($rules)
    {
        $me = $this;
        array_map(function ($field) use ($rules, $me) {
            $me->mapFieldRules($field, $rules[$field]);
        }, array_keys($rules));
    }
}
