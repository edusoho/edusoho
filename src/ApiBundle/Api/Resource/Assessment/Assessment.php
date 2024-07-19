<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\Builder\RandomTestpaperBuilder;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class Assessment extends AbstractResource
{
    public function get(ApiRequest $request, $id)
    {
        $assessment = $this->getAssessmentService()->getAssessment($id);
        $assessment['assessmentGenerateRule'] = $this->getAssessmentGenerateRuleService()->getAssessmentGenerateRuleByAssessmentId($id);

        return $assessment;
    }

    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        $this->initializeFields($fields);
        $this->validate($fields);

        $questionBank = $this->getQuestionBankService()->getQuestionBank($fields['questionBankId']);
        if (empty($questionBank['itemBank'])) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }
        $this->generateAssessment($fields, $questionBank);

        return ['ok' => true];
    }

    private function generateAssessment($fields, $questionBank)
    {
        $generateType = $fields['type'] ?? 'default';
        $methodName = 'generate'.ucfirst($generateType).'Assessment';

        $this->$methodName($fields, $questionBank);
    }

    private function generateRandomAssessment($fields, $questionBank)
    {
        $fields = array_merge($fields, [
            'itemBankId' => $questionBank['itemBankId'],
            'status' => 'generating',
        ]);
        if (!$this->check($fields)) {
            throw AssessmentException::CHECK_FAILED();
        }
        try {
            $this->biz['db']->beginTransaction();
            $assessment = $this->getRandomTestPaperBuilder()->build($fields);

            $this->createAssessmentGenerateRule($fields, $assessment);

            $this->getSchedulerService()->register([
                'name' => 'RandomAssessmentCreateJob_'.$assessment['id'],
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time() + 10),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Testpaper\Job\RandomAssessmentCreateJob',
                'args' => ['assessmentId' => $assessment['id'], 'questionBankId' => $fields['questionBankId']],
            ]);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
    }

    private function generateAiPersonalityAssessment($fields, $questionBank)
    {
        $counts = $fields['questionCategoryCounts'][0]['counts'];
        if (empty($counts)) {
            throw CommonException::ERROR_PARAMETER();
        }
        $assessment = array_merge($fields, [
            'bank_id' => $questionBank['itemBankId'],
            'created_user_id' => $this->getCurrentUser()->getId(),
            'item_count' => array_sum(array_values($counts)),
            // question_count 并不准确，受材料题子题数量影响，这里直接设置为0
            'question_count' => '0',
            'displayable' => '1',
        ]);
        try {
            $this->biz['db']->beginTransaction();
            $assessment = $this->getAssessmentService()->createBasicAssessment($assessment);
            $this->createAssessmentGenerateRule($fields, $assessment);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
    }

    public function update(ApiRequest $request, $id)
    {
        $assessment = $this->getAssessmentService()->getAssessment($id);
        if ('draft' != $assessment['status']) {
            throw AssessmentException::STATUS_ERROR();
        }

        try {
            $this->biz['db']->beginTransaction();
            if ('random' == $assessment['type']) {
                $this->getAssessmentService()->deleteAssessmentByParentId($id);
            }
            $this->getAssessmentService()->deleteAssessment($id);
            $this->add($request);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return ['ok' => true];
    }

    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (empty($conditions['itemBankId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $conditions['displayable'] = 1;
        $conditions['parent_id'] = 0;
        $conditions['bank_id'] = $conditions['itemBankId'];
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $total = $this->getAssessmentService()->countAssessments($conditions);
        if (!empty($conditions['createdUser'])) {
            $userIds = $this->getUserService()->searchUsers(['nickname' => $conditions['createdUser']], [], 0, PHP_INT_MAX, ['id']);
            $userIds = array_column($userIds, 'id');
            $conditions['created_user_ids'] = $userIds;
            if (empty($userIds)) {
                return $this->makePagingObject([], 0, $offset, $limit);
            }
        }
        $assessments = $this->getAssessmentService()->searchAssessments(
            $conditions,
            ['created_time' => 'DESC'],
            $offset,
            $limit
        );

        $userIds = array_unique(array_column($assessments, 'created_user_id'));
        $users = $this->getUserService()->findUsersByIds($userIds);
        $ids = array_column($assessments, 'id');
        $assessmentGenerateRules = $this->getAssessmentGenerateRuleService()->findAssessmentGenerateRuleByAssessmentIds($ids);
        $assessmentRulesMap = array_column($assessmentGenerateRules, 'num', 'assessment_id');
        foreach ($assessments as &$assessment) {
            $userId = $assessment['created_user_id'];
            $assessment['created_user'] = $users[$userId] ?? null;
            $assessment['num'] = $assessmentRulesMap[$assessment['id']] ?? null;
        }

        return $this->makePagingObject($assessments, $total, $offset, $limit);
    }

    public function remove(ApiRequest $request)
    {
        $assessmentIds = $request->request->get('ids', []);
        if (empty($assessmentIds)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $assessments = $this->getAssessmentService()->findAssessmentsByIds($assessmentIds);
        foreach ($assessments as $assessment) {
            if (!in_array($assessment['status'], ['draft', 'closed', 'failure'])) {
                throw AssessmentException::STATUS_ERROR();
            }
        }
        try {
            $this->biz['db']->beginTransaction();
            $randomAssessments = array_filter($assessments, function ($assessment) {
                return 'random' === $assessment['type'];
            });
            $randomAssessmentIds = array_column($randomAssessments, 'id');
            if (!empty($randomAssessmentIds)) {
                $this->getAssessmentService()->deleteAssessmentByParentIds($randomAssessmentIds);
            }
            $this->getAssessmentService()->deleteAssessmentByIds($assessmentIds);
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }

        return ['ok' => true];
    }

    private function validate($fields)
    {
        $requiredFields = [
            'name', 'type', 'questionBankId', 'num', 'generateType',
            'questionCategoryCounts', 'scores', 'percentages',
        ];
        if (!ArrayToolkit::requireds($fields, $requiredFields)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if (empty($fields['questionBankId'])) {
            throw QuestionBankException::NOT_FOUND_BANK();
        }
        if (!$this->getQuestionBankService()->canManageBank($fields['questionBankId'])) {
            throw UserException::PERMISSION_DENIED();
        }
    }

    private function initializeFields(&$fields)
    {
        $fields['scoreType'] = [
            'choice' => 'question',
            'uncertain_choice' => 'question',
            'fill' => 'question',
        ];

        $fields['choiceScore'] = [
            'choice' => 0,
            'uncertain_choice' => 0,
            'fill' => 2,
        ];
        $fields['mode'] = 'rand';
    }

    private function check($fields)
    {
        return $this->getRandomTestPaperBuilder()->canBuild($fields);
    }

    private function createAssessmentGenerateRule($fields, $assessment)
    {
        $assessmentGenerateRule = $this->buildAssessmentGenerateRule($fields, $assessment);
        $this->getAssessmentGenerateRuleService()->createAssessmentGenerateRule($assessmentGenerateRule);
    }

    private function updateAssessmentGenerateRule($fields, $assessment)
    {
        $assessmentGenerateRule = $this->getAssessmentGenerateRuleService()->getAssessmentGenerateRuleByAssessmentId($assessment['id']);
        $updateFields = $this->buildAssessmentGenerateRule($fields, $assessment);
        $this->getAssessmentGenerateRuleService()->updateAssessmentGenerateRuleById($assessmentGenerateRule['id'], $updateFields);
    }

    private function buildAssessmentGenerateRule($fields, $assessment)
    {
        $questionSetting = [
            'questionCategoryCounts' => $fields['questionCategoryCounts'],
            'scores' => $fields['scores'],
            'scoreType' => $fields['scoreType'],
            'choiceScore' => $fields['choiceScore'],
        ];
        $assessmentGenerateRule = [
            'num' => $fields['num'],
            'type' => $fields['generateType'],
            'assessment_id' => $assessment['id'],
            'bank_id' => $assessment['bank_id'],
            'question_setting' => $questionSetting,
            'difficulty' => $fields['percentages'],
            'wrong_question_rate' => $fields['wrongQuestionRate'],
        ];

        return $assessmentGenerateRule;
    }

    /**
     * @return RandomTestpaperBuilder
     */
    private function getRandomTestPaperBuilder()
    {
        return $this->biz['testpaper_builder.random_testpaper'];
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    private function getAssessmentGenerateRuleService()
    {
        return $this->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }

    /**
     * @return AssessmentService
     */
    private function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
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

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
