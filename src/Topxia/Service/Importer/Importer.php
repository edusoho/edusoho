<?php


namespace Topxia\Service\Importer;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

abstract class Importer
{
    public abstract function import(Request $request);

    public abstract function check(Request $request);

    public abstract function getTemplate();

    public abstract function tryImport(Request $request);

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}