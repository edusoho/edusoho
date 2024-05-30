<?php

namespace Biz\Question\Traits;

use Biz\System\Service\SettingService;

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

    private function canGenerateAIAnalysisForStudent($question, $item = [])
    {
        $aiAnalysisSetting = $this->getQuestionAIAnalysisSetting();
        if (empty($aiAnalysisSetting['student_enabled'])) {
            return false;
        }

        return $this->canGenerateAIAnalysis($question, $item);
    }

    private function canGenerateAIAnalysisForTeacher($question, $item = [])
    {
        $aiAnalysisSetting = $this->getQuestionAIAnalysisSetting();
        if (empty($aiAnalysisSetting['teacher_enabled'])) {
            return false;
        }

        return $this->canGenerateAIAnalysis($question, $item);
    }

    private function canGenerateAIAnalysis($question, $item)
    {
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
