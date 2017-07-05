<?php

namespace Codeages\Biz\Framework\Validation;

use Exception;

class ValidationException extends Exception
{
    public function __construct(array $errors)
    {
        $message = [];
        foreach ($errors as $field => $error) {
            $message[] = implode(' / ', $error);
        }
        parent::__construct(implode(' / ', $message));
    }
}
