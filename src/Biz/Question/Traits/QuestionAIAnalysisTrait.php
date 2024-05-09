<?php

namespace Biz\Question\Traits;

use AppBundle\Common\TimeMachine;
use Biz\User\Constant\TokenType;
use Biz\User\Service\TokenService;

trait QuestionAIAnalysisTrait
{
    private $aiAnalysisSetting = [];

    private function getQuestionAIAnalysisSetting()
    {
        if (empty($this->aiAnalysisSetting)) {
            $this->aiAnalysisSetting = $this->getSettingService()->get('question_ai_analysis');
        }

        return $this->aiAnalysisSetting;
    }

    private function canGenerateAIAnalysis($question, $item = [])
    {
        $aiAnalysisSetting = $this->getQuestionAIAnalysisSetting();
        if (empty($aiAnalysisSetting['student_enabled'])) {
            return false;
        }
        if (!empty($question['analysis']) || !empty($question['attachments']) || !empty($item['attachments']) || empty($question['answer']) || !empty($item['includeImg'])) {
            return false;
        }
        $contents = [];
        if (!empty($question['stem'])) {
            $contents[] = $question['stem'];
        }
        if (!empty($item['stem'])) {
            $contents[] = $item['stem'];
        }
        if (!empty($question['response_points'])) {
            $contents[] = json_encode($question['response_points']);
        }
        if (!empty($question['metas'])) {
            $contents[] = json_encode($question['metas']);
        }
        foreach ($contents as $content) {
            if (preg_match('/<img .*?>/', $content)) {
                return false;
            }
        }

        return true;
    }

    private function generateAIAnalysisTokens($questionIds)
    {
        $tokenMap = [];
        $tokenType = TokenType::QUESTION_AI_ANALYSIS;
        $tokens = $this->getTokenService()->findTokensByUserIdAndType($this->getCurrentUser()->getId(), $tokenType);
        foreach ($tokens as $token) {
            if (in_array($token['data']['questionId'], $questionIds)) {
                $tokenMap[$token['data']['questionId']] = $token['token'];
            }
        }
        $questionIds = array_diff($questionIds, array_keys($tokenMap));
        $args = [];
        foreach ($questionIds as $questionId) {
            $args[] = [
                'data' => ['questionId' => $questionId],
                'userId' => $this->getCurrentUser()->getId(),
                'duration' => TimeMachine::ONE_DAY,
            ];
        }
        $tokens = $this->getTokenService()->makeTokens($tokenType, $args);
        foreach ($tokens as $token) {
            $tokenMap[$token['data']['questionId']] = $token['token'];
        }

        return $tokenMap;
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
