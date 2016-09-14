<?php
namespace Topxia\Common\Exception;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class BaseException extends HttpException
{
    public function __construct($statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        parent::__construct($statusCode, $this->getServiceKernel()->trans($message), $previous, $headers, $code);
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
