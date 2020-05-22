<?php

namespace Codeages\Biz\ItemBank\Util\Validator;

use InvalidArgumentException;
use Throwable;

class ValidatorException extends InvalidArgumentException
{
    private $errors = array();

    public function __construct($errors, $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->generateMessage($errors), $code, $previous);
        $this->errors = $errors;
    }

    public function generateMessage(array $errors)
    {
        $message = '';
        foreach ($errors as $error) {
            $message .= implode(', ', $error);
        }

        return "Validate failed ({$message}).";
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
