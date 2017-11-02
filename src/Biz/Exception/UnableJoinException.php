<?php

namespace Biz\Exception;

use Codeages\Biz\Framework\Service\Exception\ServiceException;

class UnableJoinException extends ServiceException
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, 2777, $previous);
    }
}
