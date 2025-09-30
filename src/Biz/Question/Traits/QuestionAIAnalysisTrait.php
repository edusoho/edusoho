<?php

namespace Biz\Question\Traits;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use Biz\Activity\Service\ActivityService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\System\Service\SettingService;
use Biz\WrongBook\Service\WrongQuestionService;

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
        if (empty(array_filter($question['answer'], function ($answer) {
            return $answer !== '';
        }))) {
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
        if (preg_match('/ data-tex=/', json_encode($question))) {
            return false;
        }

        return true;
    }

    private function isAgentActive($answerSceneId)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerSceneId);
        if (!empty($activity)) {
            return $this->isCourseAgentActive($activity['fromCourseId']);
        }
        $module = $this->getItemBankExerciseModuleService()->getByAnswerSceneId($answerSceneId);
        if (!empty($module)) {
            $exerciseBinds = $this->getItemBankExerciseService()->findExerciseBindByExerciseId($module['exerciseId']);
            if (empty($exerciseBinds)) {
                return false;
            }
            foreach ($exerciseBinds as $exerciseBind) {
                if ('course' != $exerciseBind['bindType']) {
                    continue;
                }
                if ($this->isCourseAgentActive($exerciseBind['bindId'])) {
                    return true;
                }
            }
            return false;
        }
        $pool = $this->getWrongQuestionService()->getPoolBySceneId($answerSceneId);
        if (empty($pool) || 'course' != $pool['target_type']) {
            return false;
        }

        return $this->isCourseAgentActive($pool['target_id']);
    }

    private function isCourseAgentActive($courseId)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);

        return !empty($agentConfig['isActive']);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return ExerciseModuleService
     */
    private function getItemBankExerciseModuleService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseService
     */
    private function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->getBiz()->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->getBiz()->service('AgentBundle:AgentConfig:AgentConfigService');
    }
}
