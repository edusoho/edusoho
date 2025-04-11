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
     * AI应用文本补全（阻塞式）
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
        return $this->request('POST', '/api/open/app/completion', array('app' => $app, 'inputs' => $inputs, 'responseMode' => 'blocking'));
    }

    /**
     * AI应用文本补全（SSE 流式）
     * @param $app
     * @param $inputs
     * @return array
     * @throws ClientException
     * @throws ResponseException
     * @throws SDKException
     */
    public function startAppCompletionStream($app, $inputs)
    {
        return $this->request('POST', '/api/open/app/completion', array('app' => $app, 'inputs' => $inputs, 'responseMode' => 'streaming'), [], 'root', true);
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
        $this->request('POST', '/api/open/app/stopCompletion', array('app' => $app, 'messageId' => $messageId, 'taskId' => $taskId));
    }

    public function enableTenant()
    {
        return $this->request('POST', '/v1/tenant/enable');
    }

    public function disableTenant()
    {
        return $this->request('POST', '/v1/tenant/disable');
    }

    public function inspectTenant()
    {
        return $this->request('GET', '/v1/tenant/inspect');
    }

    public function findDomains($category)
    {
        return $this->request('GET', '/v1/domain/listByCategory', ['category' => $category]);
    }

    public function runWorkflow($workflow, $inputs)
    {
        return $this->request('POST', '/v1/workflow/run', ['workflow' => $workflow, 'inputs' => $inputs]);
    }

    public function asyncRunWorkflow($workflow, $inputs, $callback)
    {
        return $this->request('POST', '/v1/workflow/asyncRun', ['workflow' => $workflow, 'inputs' => $inputs, 'callback' => $callback]);
    }

    public function createDataset($extId, $name, $domainId, $autoIndex)
    {
        return $this->request('POST', '/v1/dataset/create', ['extId' => $extId, 'name' => $name, 'domainId' => $domainId, 'autoIndex' => $autoIndex]);
    }

    public function getDataset($id)
    {
        return $this->request('GET', '/v1/dataset/get', ['id' => $id]);
    }

    public function updateDataset($id, array $params)
    {
        $data = [
            'id' => $id,
        ];
        foreach (['name', 'domainId', 'autoIndex'] as $key) {
            if (isset($params[$key])) {
                $data[$key] = $params[$key];
            }
        }

        return $this->request('POST', '/v1/dataset/update', $data);
    }

    public function deleteDataset($id)
    {
        return $this->request('POST', '/v1/dataset/delete', ['id' => $id]);
    }

    public function createDocumentByText($datasetId, $extId, $name, $content)
    {
        return $this->request('POST', '/v1/dataset/document/createByText', ['datasetId' => $datasetId, 'extId' => $extId, 'name' => $name, 'content' => $content]);
    }

    public function createDocumentByObject($datasetId, $extId, $name, $objectKey)
    {
        return $this->request('POST', '/v1/dataset/document/createByObject', ['datasetId' => $datasetId, 'name' => $name, 'objectKey' => $objectKey, 'objectVendor' => 'escloud', 'extId' => $extId]);
    }

    public function batchCreateDocumentByObject($datasetId, $objects)
    {
        return $this->request('POST', '/v1/dataset/document/createByObjects', ['datasetId' => $datasetId, 'objects' => $objects]);
    }

    public function deleteDocument($id)
    {
        return $this->request('POST', '/v1/dataset/document/delete', ['id' => $id]);
    }

    public function pushMessgae($domainId, $userId, $contentType, $content, $push)
    {
        return $this->request('POST', '/v1/message/push', ['domainId' => $domainId, 'userId' => $userId, 'contentType' => $contentType, 'content' => $content, 'push' => $push]);
    }

    public function batchPushMessage(array $params)
    {
        return $this->request('POST', '/v1/message/batchPush', $params);
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
