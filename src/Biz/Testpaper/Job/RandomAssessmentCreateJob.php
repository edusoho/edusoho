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
        file_put_contents("/tmp/jc123", '1', 8);
        $assessment = $this->getAssessmentService()->getAssessment($this->args['assessmentId']);
        file_put_contents("/tmp/jc123", '2', 8);
        $assessmentGenerateRule = $this->getAssessmentGenerateRuleService()->getAssessmentGenerateRuleByAssessmentId($assessment['id']);
        file_put_contents("/tmp/jc123", '3', 8);
        $questionBank = $this->getQuestionBankService()->getQuestionBank($assessment['id']);
        file_put_contents("/tmp/jc123", '4', 8);
        $assessmentParams = [
            'itemBankId' => $questionBank['itemBankId'],
            'type' => 'random',
            'status' => 'generating',
            'parent_id' => $assessment['id'],
            'sections' => $assessmentGenerateRule['question_setting']['sections'],
            'scores' => $assessmentGenerateRule['question_setting']['scores'],
            'scoreType' => $assessmentGenerateRule['question_setting']['scoreType'],
            'choiceScore' => $assessmentGenerateRule['question_setting']['choiceScore']
        ];
        file_put_contents("/tmp/jc123", json_encode($assessmentParams), 8);
        for ($i = 0; $i < $assessmentGenerateRule['num']; $i++) {
            file_put_contents("/tmp/jc123", '______', 8);
            $this->biz['testpaper_builder.random_testpaper']->build($assessmentParams);
            file_put_contents("/tmp/jc123", '(((((______)))))', 8);
        }
        $this->getAssessmentService()->updateAssessment($assessment['id'], array('status' => 'draft'));
        $this->getSchedulerService()->deleteJobByName('RandomAssessmentCreateJob_'.$assessment['id']);
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

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}