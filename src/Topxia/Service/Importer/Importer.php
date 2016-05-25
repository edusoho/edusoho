<?php


namespace Topxia\Service\Importer;


use Topxia\Service\Common\ServiceKernel;

abstract class Importer
{
    public abstract function import($postData);

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}