<?php

namespace Biz\AI\Service\Impl;

use Biz\AI\Dao\AIAnswerRecordDao;
use Biz\AI\Dao\AIAnswerResultDao;
use Biz\AI\Service\AIService;
use Biz\BaseService;

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
        $filterResultIds = array_column($records, 'resultId');
        $results = $this->getAIAnswerResultDao()->findByAppAndInputsHash($app, $inputsHash);
        foreach ($results as $result) {
            if (!in_array($result['id'], $filterResultIds)) {
                $this->recordNewAnswer($app, $inputs, $result['answer']);

                return $result['answer'];
            }
        }

        return '';
    }

    private function recordNewAnswer($app, $inputs, $response)
    {
        $inputsHash = $this->makeHashForInputs($inputs);
        $result = $this->getAIAnswerResultDao()->create([
            'app' => $app,
            'inputsHash' => $inputsHash,
            'answer' => $response,
        ]);
        $this->getAIAnswerRecordDao()->create([
            'userId' => $this->getCurrentUser()->getId(),
            'app' => $app,
            'inputsHash' => $inputsHash,
            'resultId' => $result['id'],
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
