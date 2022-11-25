<?php

namespace Biz\Util;

use Biz\Common\CommonException;
use Biz\Common\JsonLogger;
use ESLive\SDK\ESLiveApi;
use ESLive\SDK\SDKException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class EsLiveClient
{
    protected $esLiveApi;

    protected $logger;

    public function __call($method, $arguments)
    {
        if (!method_exists($this->createEsLiveApi(), $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        try {
            return call_user_func_array([$this->createEsLiveApi(), $method], $arguments);
        } catch (SDKException $exception) {
            $this->getLogger()->error($exception->getMessage(), ['api' => $method, 'args' => $arguments, 'errorCode' => $exception->getErrorCode(), 'traceId' => $exception->getTraceId()]);
            return [];
        }
    }

    protected function createEsLiveApi()
    {
        if (empty($this->esLiveApi)) {
            $storage = $this->getSettingService()->get('storage', []);
            $this->esLiveApi = new ESLiveApi($storage['cloud_access_key'] ?? '', $storage['cloud_secret_key'] ?? '', ['endpoint' => $storage['es_live_api_server'] ?? '']);
        }

        return $this->esLiveApi;
    }

    protected function getLogger()
    {
        if (empty($this->logger)) {
            $stream = new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/eslive-api-error.log', Logger::ERROR);
            $this->logger = new JsonLogger('ESLiveAPI', $stream);
        }

        return $this->logger;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
