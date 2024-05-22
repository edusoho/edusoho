<?php

namespace Biz\AI\Service\Impl;

use Biz\AI\Constant\AIApp;
use Biz\AI\Dao\AIAnswerRecordDao;
use Biz\AI\Dao\AIAnswerResultDao;
use Biz\AI\DifyClient;
use Biz\AI\Service\AIService;
use Biz\BaseService;

class AIServiceImpl extends BaseService implements AIService
{
    const MAX_AI_ANALYSIS_COUNT = 10;

    public function generateAnswer($app, $inputs)
    {
        $client = new DifyClient();
        $apiKey = $this->getApiKey($app);
        $response = $client->request($apiKey, $inputs);
        $this->recordNewAnswer($app, $inputs, $response);
    }

    public function stopGeneratingAnswer($messageId, $taskId)
    {
        // TODO: Implement stopGeneratingAnswer() method.
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

    private function makeHashForInputs($inputs)
    {
        return md5(json_encode($inputs));
    }

    private function getApiKey($app)
    {
        return [
            AIApp::CHOICE_QUESTION_GENERATE_ANALYSIS => 'app-ZUPDQvGYF3PcgY6Sc6jWdYYG',
            AIApp::DETERMINE_QUESTION_GENERATE_ANALYSIS => 'app-0X6dgzpV7HT3OZXdTPqUb4vf',
            AIApp::FILL_QUESTION_GENERATE_ANALYSIS => 'app-GSprIyFdeR4d16oEx9q0Vgnl',
            AIApp::ESSAY_QUESTION_GENERATE_ANALYSIS => 'app-kw88CnoQyGNbitJeufbhJNWk',
            AIApp::MATERIAL_CHOICE_QUESTION_GENERATE_ANALYSIS => 'app-9HSoFWPkCz1oxsXGR5BaxWIv',
            AIApp::MATERIAL_DETERMINE_QUESTION_GENERATE_ANALYSIS => 'app-R3FmD4HT7mDKdCV5d0YxO5wn',
            AIApp::MATERIAL_FILL_QUESTION_GENERATE_ANALYSIS => 'app-nyHtAtvfBDwiAuJa6UBSg3xb',
            AIApp::MATERIAL_ESSAY_QUESTION_GENERATE_ANALYSIS => 'app-rITjE4uU0E9jNNkqQ3yUNaoy',
        ][$app];
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
