<?php
namespace Topxia\Common\Exception;

class RedirectException extends BaseException
{
    public function __construct($message = 'redirect', $code = 0, array $headers = array())
    {
        parent::__construct(302, $message, null, $headers, $code);
    }
}
