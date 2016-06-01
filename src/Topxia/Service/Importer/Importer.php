<?php


namespace Topxia\Service\Importer;


use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceException;
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

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function render($view, $params = array())
    {
        global $kernel;
        return $kernel->getContainer()->get('templating')->renderResponse($view, $params);
    }

    protected function createDangerResponse($message)
    {
        if(!is_string($message)){
            throw new ServiceException('message must be a string');
        }

        return array(
            'status' => self::DANGER_STATUS,
            'message' => $message
        );
    }

    protected function createErrorResponse(array $errorInfo)
    {
        return array(
            'status' => self::ERROR_STATUS,
            'errorInfo' => $errorInfo
        );
    }

    protected function createSuccessResponse(array $importData, array $checkInfo, array $customParams = array())
    {
        $response = array(
            'status' => self::SUCCESS_STATUS,
            'importData' => $importData,
            'checkInfo' => $checkInfo
        );
        $response = array_merge($customParams, $response);
        return $response;
    }

    protected function trim($data)
    {
        $data = trim($data);
        $data = str_replace(" ", "", $data);
        $data = str_replace('\n', '', $data);
        $data = str_replace('\r', '', $data);
        $data = str_replace('\t', '', $data);

        return $data;
    }
}