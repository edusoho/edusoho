<?php

namespace AppBundle\Component\MediaParser;

use AppBundle\Common\Exception\BaseException;

class ParseException extends BaseException
{
    public function __construct($message, $code = 0, array $headers = array())
    {
        parent::__construct(500, $message, null, $headers, $code);
    }
}
