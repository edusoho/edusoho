<?php

namespace AppBundle\Common\Exception;

class AccessDeniedException extends BaseException
{
    public function __construct($message = 'Access Denied', $code = 0, array $headers = array())
    {
        parent::__construct(403, $message, null, $headers, $code);
    }
}
