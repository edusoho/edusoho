<?php

namespace Biz\ItemBankExercise\Service\Impl;

use Biz\Accessor\AccessorInterface;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\Testpaper\Builder\RandomTestpaperBuilder;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentGenerateRuleService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AssessmentExerciseServiceImpl extends BaseService implements AssessmentExerciseService
{
    public function findByModuleId($moduleId)
    {
        return $this->getItemBankAssessmentExerciseDao()->findByModuleId($moduleId);
    }

    public function findByModuleIds($moduleIds)
    {
        return $this->getItemBankAssessmentExerciseDao()->findByModuleIds($moduleIds);
    }

    public function findByExerciseIdAndModuleId($exerciseId, $moduleId)
    {
        return $this->getItemBankAssessmentExerciseDao()->findByExerciseIdAndModuleId($exerciseId, $moduleId);
    }

    public function getByModuleIdAndAssessmentId($moduleId, $assessmentId)
    {
        return $this->getItemBankAssessmentExerciseDao()->getByModuleIdAndAssessmentId($moduleId, $assessmentId);
    }

    public function search($conditions, $sort, $start, $limit, $columns = [])
    {
        return $this->getItemBankAssessmentExerciseDao()->search($conditions, $sort, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getItemBankAssessmentExerciseDao()->count($conditions);
    }

    public function startAnswer($moduleId, $assessmentId, $userId)
    {
        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);
        if ('aiPersonality' == $assessment['type']) {
            $assessmentGenerateRule = $this->getAssessmentGenerateRuleService()->getAssessmentGenerateRuleByAssessmentId($assessment['id']);
            $assessmentParams = [
                    'itemBankId' => $assessment['bank_id'],
                    'type' => 'aiPersonality',
                    'name' => $assessment['name'],
                    'description' => $assessment['description'],
                    'mode' => 'rand',
                    'status' => 'open',
                    'parentId' => $assessment['id'],
                    'questionCategoryCounts' => $assessmentGenerateRule['question_setting']['questionCategoryCounts'],
                    'scores' => $assessmentGenerateRule['question_setting']['scores'],
                    'scoreType' => $assessmentGenerateRule['question_setting']['scoreType'],
                    'choiceScore' => $assessmentGenerateRule['question_setting']['choiceScore'],
                    'displayable' => '0',
                ];
            $userId = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];
            $AnswerRecordIds = $this->getAnswerRecordService()->search(['assessmentId' => $assessmentId, 'userId' => $userId], [], 0, PHP_INT_MAX, ['id']);
            $answerQuestionReports = $this->getAnswerQuestionReportService()->search(['answer_record_ids' => array_column($AnswerRecordIds, 'id'), 'status' => 'wrong'], [], 0, PHP_INT_MAX);
            if (empty($answerQuestionReports)) {
                $assessment = $this->getRandomTestPaperBuilder()->build($assessmentParams);
            } else {
                $assessmentParams['itemIds'] = array_column($answerQuestionReports, 'item_id');
                $items = $this->getItemService()->findItemsByIds($assessmentParams['itemIds']);
                // 查询所有的题目类型、每种类型按答错次数排序，然后计算是否欠缺
                // 创建一个空数组用于存储每题的错误次数
                $wrongCountsByType = [];

                // 遍历答题报告，统计每题的错误次数
                foreach ($items as $item) {
                    $itemId = $item['id'];
                    $type = $item['type'];

                    // 如果类型不存在于数组中，则添加类型键
                    if (!isset($wrongCountsByType[$type])) {
                        $wrongCountsByType[$type] = [];
                    }

                    // 直接添加或更新错误次数，但只保存itemId和计数
                    if (!isset($wrongCountsByType[$type][$itemId])) {
                        $wrongCountsByType[$type][$itemId] = 0;
                    }
                    ++$wrongCountsByType[$type][$itemId];
                }

                // 对每个类型的错误次数进行降序排序
                foreach ($wrongCountsByType as $type => &$items) {
                    arsort($items);
                }

                $targetQuestionsByType = [];
                foreach ($assessmentGenerateRule['question_setting']['questionCategoryCounts'][0]['counts'] as $questionType => $count) {
                    // 计算目标问题数量
                    $targetCount = round($count * $assessmentGenerateRule['wrong_question_rate'] / 100);
                    // 存储目标问题数量
                    $targetQuestionsByType[$questionType] = $targetCount;
                }
                // 从错误表中取出对应的题目数量
                $selectedQuestionsByType = [];
                foreach ($targetQuestionsByType as $type => $targetCount) {
                    if (isset($wrongCountsByType[$type]) && count($wrongCountsByType[$type]) >= $targetCount) {
                        $selectedQuestionsByType[$type] = array_slice($wrongCountsByType[$type], 0, $targetCount, true);
                    } else {
                        $selectedQuestionsByType[$type] = $wrongCountsByType[$type];
                    }
                }
                $assessmentParams['itemIds'] = $selectedQuestionsByType;
                $countsData = $assessmentParams['questionCategoryCounts'][0]['counts'];
                $assessmentParams['sections'] = $this->buildSections($countsData);
                $assessment = $this->getRandomTestPaperBuilder()->build($assessmentParams);
            }
        }
        if ($assessment && !$assessment['displayable']) {
            $assessmentSnapshot = $this->getAssessmentService()->getAssessmentSnapshotBySnapshotAssessmentId($assessmentId);
            if ($assessmentSnapshot) {
                $assessmentId = $assessmentSnapshot['origin_assessment_id'];
            }
        }
        $this->canStartAnswer($moduleId, $assessmentId, $userId);

        try {
            $this->beginTransaction();

            $assessmentExercise = $this->getByModuleIdAndAssessmentId($moduleId, $assessmentId);
            $module = $this->getItemBankExerciseModuleService()->get($moduleId);
            $answerRecord = $this->getAnswerService()->startAnswer($module['answerSceneId'], $assessment['id'], $userId);

            $assessmentExerciseRecord = $this->getItemBankAssessmentExerciseRecordService()->create([
                'moduleId' => $moduleId,
                'exerciseId' => $module['exerciseId'],
                'assessmentId' => $assessmentId,
                'assessmentExerciseId' => $assessmentExercise['id'],
                'userId' => $userId,
                'answerRecordId' => $answerRecord['id'],
            ]);

            $this->getUserFootprintService()->createUserFootprint([
                'targetType' => 'item_bank_assessment_exercise',
                'targetId' => $assessmentExercise['id'],
                'event' => 'answer.started',
                'userId' => $assessmentExerciseRecord['userId'],
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $assessmentExerciseRecord;
    }

    protected function buildSections($countsData)
    {
        $sections = [];
        $chineseNames = [
            'single_choice' => '单选题',
            'choice' => '多选题',
            'essay' => '问答题',
            'uncertain_choice' => '不定项选择题',
            'determine' => '判断题',
            'fill' => '填空题',
            'material' => '材料题',
        ];
        foreach ($countsData as $key => $value) {
            $sections[$key] = [
                'count' => (int) $value, // 确保 count 是整数
                'name' => $chineseNames[$key], // 根据 key 从映射数组中获取中文名称
            ];
        }

        return $sections;
    }

    public function addAssessments($exerciseId, $moduleId, $assessments)
    {
        try {
            $this->beginTransaction();

            foreach ($assessments as $assessment) {
                if ($this->getItemBankAssessmentExerciseDao()->isAssessmentExercise($moduleId, $assessment['id'], $exerciseId)) {
                    $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXERCISE_EXIST());
                }

                $this->getItemBankAssessmentExerciseDao()->create(
                    [
                        'exerciseId' => $exerciseId,
                        'moduleId' => $moduleId,
                        'assessmentId' => $assessment['id'],
                    ]
                );
            }
            $this->dispatchEvent('assessmentExercise.create', $assessments, ['exerciseId' => $exerciseId, 'moduleId' => $moduleId]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function isAssessmentExercise($moduleId, $assessmentId, $exerciseId)
    {
        $assessmentExercise = $this->getItemBankAssessmentExerciseDao()->isAssessmentExercise($moduleId, $assessmentId, $exerciseId);

        return empty($assessmentExercise) ? false : true;
    }

    protected function canStartAnswer($moduleId, $assessmentId, $userId)
    {
        $module = $this->getItemBankExerciseModuleService()->get($moduleId);
        if (empty($module) || ExerciseModuleService::TYPE_ASSESSMENT != $module['type']) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!$this->count(['moduleId' => $moduleId, 'assessmentId' => $assessmentId])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $access = $this->getItemBankExerciseService()->canLearnExercise($module['exerciseId']);
        if (AccessorInterface::SUCCESS != $access['code']) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($module['exerciseId']);
        if (0 == $itemBankExercise['assessmentEnable']) {
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_EXERCISE_CLOSED());
        }

        $latestRecord = $this->getItemBankAssessmentExerciseRecordService()->getLatestRecord($moduleId, $assessmentId, $userId);
        if (!empty($latestRecord) && AnswerService::ANSWER_RECORD_STATUS_FINISHED != $latestRecord['status']) {
            $this->createNewException(ItemBankExerciseException::ASSESSMENT_ANSWER_IS_DOING());
        }

        return false;
    }

    public function deleteAssessmentExercise($id)
    {
        $assessmentExercise = $this->getItemBankAssessmentExerciseDao()->get($id);
        if (empty($assessmentExercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $result = $this->getItemBankAssessmentExerciseDao()->delete($id);
        $this->dispatchEvent('assessmentExercise.delete', $assessmentExercise);

        return $result;
    }

    public function batchDeleteAssessmentExercise($ids)
    {
        if (empty($ids)) {
            return;
        }

        $this->getItemBankAssessmentExerciseDao()->batchDelete(['ids' => $ids]);
    }

    public function getAssessmentCountGroupByExerciseId($ids)
    {
        return $this->getItemBankAssessmentExerciseDao()->getAssessmentCountGroupByExerciseId($ids);
    }

    public function deleteByAssessmentId($assessmentId)
    {
        $assessment = $this->getItemBankAssessmentExerciseDao()->getByAssessmentId($assessmentId);
        $result = $this->getItemBankAssessmentExerciseDao()->deleteByAssessmentId($assessmentId);

        if (!empty($assessment)) {
            $this->dispatch('assessmentExercise.delete', $assessment);
        }

        return $result;
    }

    public function deleteByAssessmentIds($assessmentIds)
    {
        if (empty($assessmentIds)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getItemBankAssessmentExerciseDao()->batchDelete(['assessmentIds' => $assessmentIds]);
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseRecordService');
    }

    /**
     * @return AssessmentExerciseDao
     */
    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->createDao('ItemBankExercise:AssessmentExerciseDao');
    }

    /**
     * @return \Biz\User\UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->createService('User:UserFootprintService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentGenerateRuleService
     */
    protected function getAssessmentGenerateRuleService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentGenerateRuleService');
    }

    /**
     * @return RandomTestpaperBuilder
     */
    private function getRandomTestPaperBuilder()
    {
        return $this->biz['testpaper_builder.random_testpaper'];
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }
}
