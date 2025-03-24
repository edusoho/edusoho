<?php

namespace Biz\AI\Service;

interface AIService
{
    public function inspectAccount();

    public function enableAccount();

    public function disableAccount();

    public function generateAnswer($app, $inputs);

    public function stopGeneratingAnswer($app, $messageId, $taskId);

    public function needGenerateNewAnswer($app, $inputs);

    public function getAnswerFromLocal($app, $inputs);

    public function enableTenant();

    public function disableTenant();

    public function inspectTenant();

    public function findDomains($category);

    public function runWorkflow($alias, array $data);

    public function createDataset(array $params);

    public function getDataset($id);

    public function updateDataset($id, array $params);

    public function deleteDataset($id);

    public function createDocumentByText(array $params);

    public function createDocumentByResource(array $params);

    public function batchCreateDocumentByResource($datasetId, $items);

    public function deleteDocument($id);
}
