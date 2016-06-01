<?php


namespace Topxia\Service\Importer;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

abstract class Importer
{
    const DANGER_STATUS = 'danger';
    const ERROR_STATUS = 'error';
    const SUCCESS_STATUS = 'success';

    public abstract function import(Request $request);

    public abstract function check(Request $request);

    public abstract function getTemplate(Request $request);

    public abstract function tryImport(Request $request);

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function render($view, $params = array())
    {
        global $kernel;
        return $kernel->getContainer()->get('templating')->renderResponse($view, $params);
    }
}