<?php

namespace Biz\Importer;

use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\Context\Biz;

abstract class Importer
{
    const DANGER_STATUS = 'danger';
    const ERROR_STATUS = 'error';
    const SUCCESS_STATUS = 'success';

    protected $biz;

    abstract public function import(Request $request);

    abstract public function check(Request $request);

    abstract public function getTemplate(Request $request);

    abstract public function tryImport(Request $request);

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function render($view, $params = array())
    {
        global $kernel;

        return $kernel->getContainer()->get('templating')->renderResponse($view, $params);
    }

    protected function createDangerResponse($message)
    {
        if (!is_string($message)) {
            throw new ServiceException('Message must be a string');
        }

        return array(
            'status' => self::DANGER_STATUS,
            'message' => $message,
        );
    }

    protected function createErrorResponse(array $errorInfo)
    {
        return array(
            'status' => self::ERROR_STATUS,
            'errorInfo' => $errorInfo,
        );
    }

    protected function createSuccessResponse(array $importData, array $checkInfo, array $customParams = array())
    {
        $response = array(
            'status' => self::SUCCESS_STATUS,
            'importData' => $importData,
            'checkInfo' => $checkInfo,
        );
        $response = array_merge($customParams, $response);

        return $response;
    }

    protected function trim($data)
    {
        $data = trim($data);
        $data = str_replace(' ', '', $data);
        $data = str_replace('\n', '', $data);
        $data = str_replace('\r', '', $data);
        $data = str_replace('\t', '', $data);

        return $data;
    }
}
