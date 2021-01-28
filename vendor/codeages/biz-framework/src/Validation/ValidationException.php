<?php

namespace Codeages\Biz\Framework\Validation;

use Exception;

/**
 * @deprecated
 */
class ValidationException extends Exception
{
    public function __construct(array $errors)
    {
        $message = array();
        foreach ($errors as $field => $error) {
            $message[] = implode(' / ', $error);
        }
        parent::__construct(implode(' / ', $message));
    }
}
