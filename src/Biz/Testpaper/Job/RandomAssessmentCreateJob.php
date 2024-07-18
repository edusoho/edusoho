<?php

namespace Biz\Testpaper\Job;

use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class RandomAssessmentCreateJob extends AbstractJob
{
    public function execute()
    {
        $assessment = $this->getAssessmentService()->getAssessment($this->args['assessmentId']);
        $assessmentGenerateRule = $this->getAssessmentGenerateRuleService()->getAssessmentGenerateRuleByAssessmentId($assessment['id']);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($this->args['questionBankId']);
        $assessmentParams = [
            'itemBankId' => $questionBank['itemBankId'],
            'type' => 'random',
            'name' => $assessment['name'],
            'description' => $assessment['description'],
            'mode' => 'rand',
            'status' => 'generating',
            'parentId' => $assessment['id'],
            'questionCategoryCounts' => $assessmentGenerateRule['question_setting']['questionCategoryCounts'],
            'scores' => $assessmentGenerateRule['question_setting']['scores'],
            'scoreType' => $assessmentGenerateRule['question_setting']['scoreType'],
            'choiceScore' => $assessmentGenerateRule['question_setting']['choiceScore'],
            'displayable' => '0',
        ];

        try {
            $this->biz['db']->beginTransaction();
            for ($i = 0; $i < $assessmentGenerateRule['num'] - 1; ++$i) {
                $this->biz['testpaper_builder.random_testpaper']->build($assessmentParams);
            }
            $this->getAssessmentService()->updateAssessment($assessment['id'], ['status' => 'draft']);
            $this->getAssessmentService()->updateBasicAssessmentByParentId($assessment['id'], ['status' => 'draft']);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $this->getAssessmentService()->updateAssessment($assessment['id'], ['status' => 'failure']);
            throw $e;
        }
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    protected function getAssessmentGenerateRuleService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }

    /**
     * @return QuestionBankService
     */
    private function getQuestionBankService()
    {
        return $this->biz->service('QuestionBank:QuestionBankService');
    }
}
