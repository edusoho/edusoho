<?php

namespace ApiBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class QuestionBankRandomTestpaper extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw UserException::PERMISSION_DENIED();
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }
        $fields = array_merge(
            $request->request->all(),
            [
                'itemBankId' => $questionBank['itemBankId'],
                'type' => 'random',
                'status' => 'generating'
            ]
        );
        $assessment = $this->getBiz()['testpaper_builder.random_testpaper']->build($fields);
        // 创建试卷，状态为创建中
//        if ($fields['type'] == 'randomTestpaper') {
        $question_setting[] = [
            'sections' => $fields['sections'],
            'scores' => $fields['scores'],
            'scoreType' => $fields['scoreType'],
            'choiceScore' => $fields['choiceScore']
        ];
        $assessmentGenerateRule = [
            'num' => $fields['num'],
            'type' => $fields['generateType'],
            'assessment_id' => $assessment['id'],
            'question_setting' => $question_setting,
            'difficulty' => $fields['percentages'],
            'wrong_question_rate' => $fields['wrongQuestionRate'],
        ];
//        }

        // 保存配置
        $this->getAssessmentGenerateRuleService()->createAssessmentGenerateRule($assessmentGenerateRule);
        // 创建JOB
        $this->getSchedulerService()->register(array(
            'name' => 'RandomAssessmentCreateJob_'.$assessment['id'],
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time() + 10),
            'misfire_policy' => 'executing',
            'class' => 'Biz\Testpaper\Job\RandomAssessmentCreateJob',
            'args' => ["assessmentId" => $assessment['id']],
        ));
        // 返回成功
        return 'true';
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    private function getAssessmentGenerateRuleService()
    {
        return $this->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }

    /**
     * @return QuestionBankService
     */
    private function getQuestionBankService()
    {
        return $this->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->service('Scheduler:SchedulerService');
    }
}
