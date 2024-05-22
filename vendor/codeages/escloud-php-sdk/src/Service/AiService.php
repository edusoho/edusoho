<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\ClientException;

/**
 * AI服务
 */
class AIService extends BaseService
{
    protected $host = 'ai-service.edusoho.net';

    protected $service = 'AI';

    /**
     * 开通 AI 服务
     * @return void
     * @throws ResponseException
     * @throws SDKException
     * @throws ClientException
     */
    public function enableAccount()
    {
        $this->request('POST', '/api/open/account/enable');
    }

    /**
     * 禁用 AI 服务
     * @return void
     * @throws ResponseException
     * @throws SDKException
     * @throws ClientException
     */
    public function disableAccount()
    {
        $this->request('POST', '/api/open/account/disable');
    }

    /**
     * 审视 AI 服务状态
     *
     * @return array status: none, ok, disabled
     *
     * @throws ResponseException
     * @throws SDKException
     * @throws ClientException
     */
    public function inspectAccount()
    {
        return $this->request('GET', '/api/open/account/inspect');
    }

    /**
     * 开始AI应用的文本补全输出
     *
     * @param $app
     * @param $inputs
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function startAppCompletion($app, $inputs)
    {
        $uri = sprintf('/api/open/app/%s/completion', $app);

        return $this->request('POST', $uri, array('inputs' => $inputs, 'responseMode' => 'streaming'), [], 'root', true);
    }

    /**
     * 停止AI应用的文本补全输出
     *
     * @param $app
     * @param $messageId
     * @param $taskId
     * @return void
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function stopAppCompletion($app, $messageId, $taskId)
    {
        $uri = sprintf('/api/open/app/%s/stopCompletion', $app);
        $this->request('POST', $uri, array('messageId' => $messageId, 'taskId' => $taskId));
    }

    /**
     * 生成AI应用文本补全的客户端URL
     *
     * @param $app
     * @param $lifetime
     * @return string
     */
    public function makeClientAppCompletionUrl($app, $lifetime = 60)
    {
        $params = array('token' => $this->makeClientToken($lifetime));
        return sprintf('https://%s/api/client/app/%s/completion?%s', $this->host, $app, http_build_query($params));
    }

    private function makeClientToken($lifetime)
    {
        $payload = array(
            'iss' => 'AIClient',
            'exp' => time() + $lifetime,
        );

        return $this->auth->makeJwtTokenWithKid($payload);
    }
}
