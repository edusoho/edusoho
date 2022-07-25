<?php

namespace Biz\WrongBook\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\LogService;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class WrongQuestionServiceImpl extends BaseService implements WrongQuestionService
{
    public function buildWrongQuestion($fields, $source)
    {
        try {
            $this->beginTransaction();

            $pool = $this->handleQuestionPool($source);
            $collect = $this->handleQuestionCollect(array_merge($fields, ['pool_id' => $pool['id']]));
            $wrongQuestion = $this->createWrongQuestion(array_merge($fields, [
                'collect_id' => $collect['id'],
                'user_id' => $source['user_id'],
                'answer_scene_id' => $source['answer_scene_id'],
                'testpaper_id' => $source['testpaper_id'],
            ]));
            $this->getLogService()->info(
                'wrong_question',
                'create_wrong_question',
                "创建错题#{$wrongQuestion['id']},错题id{$wrongQuestion['item_id']}",
                $wrongQuestion
            );
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatchEvent('wrong_question.create', $wrongQuestion);

        return $wrongQuestion;
    }

    public function batchBuildWrongQuestion($wrongAnswerQuestionReports, $source)
    {
//        try {
//            $this->beginTransaction();
        $pool = $this->handleQuestionPool($source);

        $wrongQuestions = $this->processWrongQuestions($wrongAnswerQuestionReports, $source, $pool);
        $this->updatePoolItemNum($pool['id']);
        $this->getLogService()->info(
                'wrong_question',
                'create_wrong_question',
                '批量创建错题',
                ArrayToolkit::column($wrongQuestions, 'id')
            );

        $this->dispatchEvent('wrong_question.batch_create', $wrongQuestions, ['pool_id' => $pool['id']]);
//            $this->commit();
//        } catch (\Exception $e) {
//            $this->rollback();
//            $this->getLogService()->error(
//                'wrong_question',
//                'create_wrong_question',
//                '批量创建错题失败',
//                ArrayToolkit::column($wrongAnswerQuestionReports, 'id')
//            );
//        }
    }

    public function batchBuildCorrectQuestion($correctAnswerQuestionReports, $source)
    {
        try {
            $this->beginTransaction();
            $pool = $this->handleQuestionPool($source);
            $this->processCollectQuestionReports($correctAnswerQuestionReports, $pool);
            $poolCollects = $this->getWrongQuestionCollectDao()->search(['pool_id' => $pool['id'], 'status' => 'wrong'], [], 0, $this->getWrongQuestionCollectDao()->count(['pool_id' => $pool['id'], 'status' => 'wrong']));
            $this->getWrongQuestionBookPoolDao()->update($pool['id'], ['item_num' => count($poolCollects)]);
            $this->getLogService()->info(
                'wrong_question',
                'correct_wrong_question',
                '修正错题',
                []
            );
            $this->dispatchEvent('wrong_question_correct.batch_create', [], ['pool_id' => $pool['id']]);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogService()->info(
                'wrong_question',
                'correct_wrong_question',
                '修正错题失败',
                ArrayToolkit::column($correctAnswerQuestionReports, 'id')
            );
        }
    }

    public function createWrongQuestion($fields)
    {
        $wrongQuestionRequireFields = [
            'collect_id',
            'user_id',
            'question_id',
            'item_id',
            'answer_scene_id',
            'testpaper_id',
            'answer_question_report_id',
        ];
        if (!ArrayToolkit::requireds($fields, $wrongQuestionRequireFields)) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }

        $wrongQuestionRequireFields = ArrayToolkit::parts($fields, $wrongQuestionRequireFields);

        return $this->getWrongQuestionDao()->create(array_merge($wrongQuestionRequireFields, ['submit_time' => time()]));
    }

    public function searchWrongQuestion($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getWrongQuestionDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function searchWrongQuestionsWithDistinctUserId($conditions, $orderBys, $start, $limit)
    {
        return $this->getWrongQuestionDao()->searchWrongQuestionsWithDistinctUserId($conditions, $orderBys, $start, $limit);
    }

    public function countWrongQuestionsWithDistinctUserId($conditions)
    {
        return $this->getWrongQuestionDao()->countWrongQuestionsWithDistinctUserId($conditions);
    }

    public function findWrongQuestionsByUserIdsAndItemIdAndSceneIds($userIds, $itemId, $sceneIds)
    {
        if (empty($userIds) || empty($itemId) || empty($sceneIds)) {
            return [];
        }

        return $this->getWrongQuestionDao()->findWrongQuestionsByUserIdsAndItemIdAndSceneIds($userIds, $itemId, $sceneIds);
    }

    public function findWrongQuestionsByUserIdAndItemIdsAndSceneIds($userId, $itemIds, $sceneIds)
    {
        if (empty($userId) || empty($itemIds) || empty($sceneIds)) {
            return [];
        }

        return $this->getWrongQuestionDao()->findWrongQuestionsByUserIdAndItemIdsAndSceneIds($userId, $itemIds, $sceneIds);
    }

    public function findWrongQuestionsByUserIdAndSceneIds($userId, $sceneIds)
    {
        if (empty($userId) || empty($sceneIds)) {
            return [];
        }

        return $this->getWrongQuestionDao()->findWrongQuestionsByUserIdAndSceneIds($userId, $sceneIds);
    }

    public function findWrongQuestionCollectByCollectIds($collectIds)
    {
        $collects = $this->getWrongQuestionCollectDao()->findWrongQuestionCollectByIds($collectIds);

        return ArrayToolkit::index($collects, 'id');
    }

    public function findWrongQuestionByCollectIds($collectIds)
    {
        $collects = $this->getWrongQuestionDao()->findWrongQuestionByCollectIds($collectIds);

        return ArrayToolkit::index($collects, 'id');
    }

    public function searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getWrongQuestionDao()->searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns);
    }

    public function searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getWrongQuestionDao()->searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $start, $limit, $columns);
    }

    public function searchWrongQuestionCollect($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getWrongQuestionCollectDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countWrongQuestionCollect($conditions)
    {
        return $this->getWrongQuestionCollectDao()->count($conditions);
    }

    public function getPool($poolId)
    {
        return $this->getWrongQuestionBookPoolDao()->get($poolId);
    }

    public function getPoolBySceneId($sceneId)
    {
        return $this->getWrongQuestionBookPoolDao()->getPoolBySceneId($sceneId);
    }

    public function updatePool($id, $pool)
    {
        return $this->getWrongQuestionBookPoolDao()->update($id, $pool);
    }

    public function countWrongQuestion($conditions)
    {
        return $this->getWrongQuestionDao()->count($conditions);
    }

    public function countWrongQuestionWithCollect($conditions)
    {
        return $this->getWrongQuestionDao()->countWrongQuestionWithCollect($conditions);
    }

    public function countWrongQuestionsWithDistinctItem($conditions)
    {
        return $this->getWrongQuestionDao()->countWrongQuestionsWithDistinctItem($conditions);
    }

    public function searchWrongBookPool($conditions, $orderBys, $start, $limit)
    {
        return $this->getWrongQuestionBookPoolDao()->searchPoolByConditions($conditions, $orderBys, $start, $limit);
    }

    public function countWrongBookPool($conditions)
    {
        return $this->getWrongQuestionBookPoolDao()->countPoolByConditions($conditions);
    }

    public function getWrongBookPoolByFieldsGroupByTargetType($fields)
    {
        return $this->getWrongQuestionBookPoolDao()->getPoolByFieldsGroupByTargetType($fields);
    }

    public function deleteWrongQuestion($id)
    {
        $wrongExisted = $this->getWrongQuestionDao()->get($id);
        if (empty($wrongExisted)) {
            throw WrongBookException::WRONG_QUESTION_NOT_EXIST();
        }

        try {
            $this->beginTransaction();

            $this->getWrongQuestionDao()->delete($id);

            $this->getLogService()->info(
                'wrong_question',
                'delete_wrong_question',
                "删除错题#{$id},错题id{$wrongExisted['item_id']}"
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatchEvent('wrong_question.delete', $wrongExisted);
    }

    public function batchDeleteWrongQuestionByItemIds($itemIds)
    {
        $wrongQuestionCollects = $this->getWrongQuestionCollectDao()->findCollectByItemIds($itemIds);

        if (empty($wrongQuestionCollects)) {
            return;
        }

        try {
            $this->beginTransaction();

            $this->getWrongQuestionDao()->batchDelete(['item_ids' => $itemIds]);
            $this->getWrongQuestionCollectDao()->batchDelete(['item_ids' => $itemIds]);

            $poolIds = array_unique(ArrayToolkit::column($wrongQuestionCollects, 'pool_id'));

            foreach ($poolIds as $poolId) {
                $this->updatePoolItemNum($poolId);
            }

            $this->getLogService()->info(
                'wrong_question',
                'delete_wrong_question',
                '错题本题目清除',
                ['item_ids' => $itemIds]
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatchEvent('wrong_question.batch_delete', $wrongQuestionCollects);
    }

    public function findWrongQuestionBySceneIds($sceneIds)
    {
        return $this->getWrongQuestionDao()->findWrongQuestionBySceneIds($sceneIds);
    }

    protected function processWrongQuestions($wrongAnswerQuestionReports, $source, $pool)
    {
        $wrongQuestions = [];
        $itemIds = ArrayToolkit::column($wrongAnswerQuestionReports, 'item_id');
        $collects = $this->getWrongQuestionCollectDao()->search(['item_ids' => empty($itemIds) ? [-1] : $itemIds, 'pool_id' => $pool['id']], [], 0, count($itemIds), ['id', 'item_id', 'wrong_times']);

        $collects = ArrayToolkit::index($collects, 'item_id');
        $createCollects = [];
        $updateCollects = [];
        foreach ($wrongAnswerQuestionReports as $wrongAnswerQuestionReport) {
            $fields = ['item_id' => $wrongAnswerQuestionReport['item_id'], 'pool_id' => $pool['id']];
            if (empty($collects[$wrongAnswerQuestionReport['item_id']])) {
                $fields['last_submit_time'] = time();
                $fields['status'] = 'wrong';
                $fields['wrong_times'] = 1;
                $createCollects[] = $fields;
            } else {
                $collect = $collects[$wrongAnswerQuestionReport['item_id']];
                $updateCollects[$collect['id']] = ['status' => 'wrong', 'last_submit_time' => time(), 'wrong_times' => $collect['wrong_times'] + 1];
            }
        }
        if (!empty($createCollects)) {
            $this->getWrongQuestionCollectDao()->batchCreate($createCollects);
        }
        if (!empty($updateCollects)) {
            $this->getWrongQuestionCollectDao()->batchUpdate(array_keys($updateCollects), $updateCollects, 'id');
        }
        $this->processWrongQuestionsWithSubmit($wrongAnswerQuestionReports, $source, $pool);

        return $wrongQuestions;
    }

    protected function processWrongQuestionsWithSubmit($wrongAnswerQuestionReports, $source, $pool)
    {
        if (empty($wrongAnswerQuestionReports)) {
            return;
        }
        $itemIds = ArrayToolkit::column($wrongAnswerQuestionReports, 'item_id');
        $collects = $this->getWrongQuestionCollectDao()->search(['item_ids' => empty($itemIds) ? [-1] : $itemIds, 'pool_id' => $pool['id']], [], 0, count($itemIds), ['id', 'item_id', 'wrong_times']);

        $collects = ArrayToolkit::index($collects, 'item_id');
        foreach ($wrongAnswerQuestionReports as $wrongAnswerQuestionReport) {
            if (empty($collects[$wrongAnswerQuestionReport['item_id']])) {
                continue;
            }
            $wrongQuestions[] = [
                'collect_id' => $collects[$wrongAnswerQuestionReport['item_id']]['id'],
                'user_id' => $source['user_id'],
                'item_id' => $wrongAnswerQuestionReport['item_id'],
                'question_id' => $wrongAnswerQuestionReport['question_id'],
                'answer_scene_id' => $source['answer_scene_id'],
                'testpaper_id' => $source['testpaper_id'],
                'answer_question_report_id' => $wrongAnswerQuestionReport['id'],
                'submit_time' => time(),
                'source_type' => $source['source_type'],
                'source_id' => $source['source_id'],
                'has_answer' => AnswerQuestionReportService::STATUS_NOANSWER != $wrongAnswerQuestionReport['status'] ? 1 : 0,
            ];
        }
        if (!empty($wrongQuestions)) {
            $this->getWrongQuestionDao()->batchCreate($wrongQuestions);
        }
    }

    protected function processCollectQuestionReports($correctAnswerQuestionReports, $pool)
    {
        $itemIds = ArrayToolkit::column($correctAnswerQuestionReports, 'item_id');
        $collects = $this->getWrongQuestionCollectDao()->search(['item_ids' => empty($itemIds) ? [-1] : $itemIds, 'pool_id' => $pool['id']], [], 0, count($itemIds), ['id', 'item_id', 'wrong_times']);
        $collects = ArrayToolkit::index($collects, 'item_id');
        $createCollects = [];
        $updateCollects = [];
        foreach ($correctAnswerQuestionReports as $correctAnswerQuestionReport) {
            $fields = ['item_id' => $correctAnswerQuestionReport['item_id'], 'pool_id' => $pool['id']];
            if (empty($collects[$correctAnswerQuestionReport['item_id']])) {
                $fields['last_submit_time'] = time();
                $fields['status'] = 'correct';
                $fields['wrong_times'] = 1;
                $createCollects[] = $fields;
            } else {
                $collect = $collects[$correctAnswerQuestionReport['item_id']];
                $updateCollects[$collect['id']] = ['status' => 'correct'];
            }
        }
        if (!empty($createCollects)) {
            $this->getWrongQuestionCollectDao()->batchCreate($createCollects);
        }
        if (!empty($updateCollects)) {
            $this->getWrongQuestionCollectDao()->batchUpdate(array_keys($updateCollects), $updateCollects, 'id');
        }
    }

    protected function updatePoolItemNum($poolId)
    {
        $poolCollects = $this->getWrongQuestionCollectDao()->search(['pool_id' => $poolId, 'status' => 'wrong'], [], 0, PHP_INT_MAX);

        $this->getWrongQuestionBookPoolDao()->update($poolId, ['item_num' => count($poolCollects)]);
    }

    protected function handleQuestionCollect($fields)
    {
        $collectRequireFields = [
            'pool_id',
            'item_id',
        ];
        if (!ArrayToolkit::requireds($fields, $collectRequireFields)) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }

        $collect = $this->getWrongQuestionCollectDao()->getCollectBYPoolIdAndItemId($fields['pool_id'], $fields['item_id']);

        if (!$collect) {
            $collectFields = ArrayToolkit::parts($fields, $collectRequireFields);
            $collectFields['last_submit_time'] = time();
            $collectFields['status'] = 'wrong';
            $collect = $this->getWrongQuestionCollectDao()->create($collectFields);
        } else {
            $this->getWrongQuestionCollectDao()->update($collect['id'], ['status' => 'wrong', 'last_submit_time' => time()]);
        }

        return $collect;
    }

    protected function handleQuestionPool($fields)
    {
        $poolRequireFields = [
            'target_type',
            'target_id',
            'user_id',
            ];
        if (!ArrayToolkit::requireds($fields, $poolRequireFields)) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }

        $pool = $this->getWrongQuestionBookPoolDao()->getPoolByUserIdAndTargetTypeAndTargetId($fields['user_id'], $fields['target_type'], $fields['target_id']);

        if (!$pool) {
            $poolFields = ArrayToolkit::parts($fields, $poolRequireFields);
            $poolFields['item_num'] = 0;
            $pool = $this->getWrongQuestionBookPoolDao()->create($poolFields);
        }

        return $pool;
    }

    /**
     * @return WrongQuestionDao
     */
    protected function getWrongQuestionDao()
    {
        return $this->createDao('WrongBook:WrongQuestionDao');
    }

    /**
     * @return WrongQuestionBookPoolDao
     */
    protected function getWrongQuestionBookPoolDao()
    {
        return $this->createDao('WrongBook:WrongQuestionBookPoolDao');
    }

    /**
     * @return WrongQuestionCollectDao
     */
    protected function getWrongQuestionCollectDao()
    {
        return $this->createDao('WrongBook:WrongQuestionCollectDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
