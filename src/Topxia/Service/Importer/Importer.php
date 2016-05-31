<?php


namespace Topxia\Service\Importer;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

abstract class Importer
{
    public abstract function import($postData);

    public abstract function check(Request $request);

    public abstract function getTemplate();

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}