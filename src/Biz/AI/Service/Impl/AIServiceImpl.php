<?php

namespace Biz\AI\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AI\Dao\AIAnswerRecordDao;
use Biz\AI\Dao\AIAnswerResultDao;
use Biz\AI\Service\AIService;
use Biz\BaseService;
use Biz\Common\CommonException;

class AIServiceImpl extends BaseService implements AIService
{
    const MAX_AI_ANALYSIS_COUNT = 10;

    public function inspectAccount()
    {
        return $this->getAIService()->inspectAccount();
    }

    public function enableAccount()
    {
        $this->getAIService()->enableAccount();
    }

    public function disableAccount()
    {
        $this->getAIService()->disableAccount();
    }

    public function generateAnswer($app, $inputs)
    {
        $response = $this->getAIService()->startAppCompletionStream($app, $inputs);
        $this->recordNewAnswer($app, $inputs, $this->makeSSE($response));
    }

    public function stopGeneratingAnswer($app, $messageId, $taskId)
    {
        $this->getAIService()->stopAppCompletion($app, $messageId, $taskId);
    }

    public function needGenerateNewAnswer($app, $inputs)
    {
        $inputsHash = $this->makeHashForInputs($inputs);
        $userId = $this->getCurrentUser()->getId();
        if ($this->getAIAnswerRecordDao()->count(['userId' => $userId, 'app' => $app, 'inputsHash' => $inputsHash]) >= self::MAX_AI_ANALYSIS_COUNT) {
            return false;
        }
        $results = $this->getAIAnswerResultDao()->findByAppAndInputsHash($app, $inputsHash);
        $records = $this->getAIAnswerRecordDao()->findByUserIdAndAppAndInputsHash($userId, $app, $inputsHash);
        if (array_diff(array_column($results, 'id'), array_column($records, 'resultId'))) {
            return false;
        }

        return true;
    }

    public function getAnswerFromLocal($app, $inputs)
    {
        $inputsHash = $this->makeHashForInputs($inputs);
        $records = $this->getAIAnswerRecordDao()->findByUserIdAndAppAndInputsHash($this->getCurrentUser()->getId(), $app, $inputsHash);
        if (count($records) >= self::MAX_AI_ANALYSIS_COUNT) {
            shuffle($records);
            $result = $this->getAIAnswerResultDao()->get($records[0]['resultId']);

            return $result['answer'];
        }
        $recordedResultIds = array_column($records, 'resultId');
        $results = $this->getAIAnswerResultDao()->findByAppAndInputsHash($app, $inputsHash);
        foreach ($results as $result) {
            if (!in_array($result['id'], $recordedResultIds)) {
                $this->recordAnswerAndUser($app, $inputsHash, $result['id']);

                return $result['answer'];
            }
        }

        return '';
    }

    public function enableTenant()
    {
        $this->getAIService()->enableTenant();
    }

    public function disableTenant()
    {
        $this->getAIService()->disableTenant();
    }

    public function inspectTenant()
    {
        return $this->getAIService()->inspectTenant();
    }

    public function findDomains($category)
    {
        $category = empty($category) ? 'vt' : $category;

        return $this->getAIService()->findDomains($category);
    }

    public function runWorkflow($alias, array $data)
    {
        return $this->getAIService()->runWorkflow($alias, $data);
    }

    public function createDataset(array $params)
    {
        if (!ArrayToolkit::requireds($params, ['externalId', 'name', 'domainId', 'autoIndex'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $this->getAIService()->createDataset($params['externalId'], $params['name'], $params['domainId'], $params['autoIndex']);
    }

    public function getDataset($id)
    {
        return $this->getAIService()->getDataset($id);
    }

    public function updateDataset(array $params)
    {
        // TODO: Implement updateDataset() method.
    }

    public function deleteDataset($id)
    {
        $this->getAIService()->deleteDataset($id);
    }

    public function createDocument(array $params)
    {
        // TODO: Implement createDocument() method.
    }

    public function deleteDocument($id)
    {
        $this->getAIService()->deleteDocument($id);
    }

    private function recordNewAnswer($app, $inputs, $response)
    {
        $inputsHash = $this->makeHashForInputs($inputs);
        $result = $this->getAIAnswerResultDao()->create([
            'app' => $app,
            'inputsHash' => $inputsHash,
            'answer' => $response,
        ]);
        $this->recordAnswerAndUser($app, $inputsHash, $result['id']);
    }

    private function recordAnswerAndUser($app, $inputsHash, $resultId)
    {
        $this->getAIAnswerRecordDao()->create([
            'userId' => $this->getCurrentUser()->getId(),
            'app' => $app,
            'inputsHash' => $inputsHash,
            'resultId' => $resultId,
        ]);
    }

    private function makeSSE($response)
    {
        $sse = '';
        foreach ($response as $data) {
            $sse .= 'data:'.json_encode($data)."\n\n";
        }

        return $sse;
    }

    private function makeHashForInputs($inputs)
    {
        return md5(json_encode($inputs));
    }

    /**
     * @return \ESCloud\SDK\Service\AIService
     */
    protected function getAIService()
    {
        return $this->biz['ESCloudSdk.ai'];
    }

    /**
     * @return AIAnswerResultDao
     */
    protected function getAIAnswerResultDao()
    {
        return $this->createDao('AI:AIAnswerResultDao');
    }

    /**
     * @return AIAnswerRecordDao
     */
    protected function getAIAnswerRecordDao()
    {
        return $this->createDao('AI:AIAnswerRecordDao');
    }
}
