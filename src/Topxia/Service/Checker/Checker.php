<?php


namespace Topxia\Service\Checker;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

abstract class Checker
{
    public abstract function check(Request $request);

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}