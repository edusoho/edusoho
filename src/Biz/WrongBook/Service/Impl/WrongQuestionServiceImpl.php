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

class WrongQuestionServiceImpl extends BaseService implements WrongQuestionService
{
    public function buildWrongQuestion($fields, $source)
    {
        $this->beginTransaction();
        try {
            $pool = $this->handleQuestionPool($source);
            $collect = $this->handleQuestionCollect(array_merge($fields, ['pool_id' => $pool['id']]));
            $wrongQuestion = $this->createWrongQuestion(array_merge($fields, [
                'collect_id' => $collect['id'],
                'user_id' => $source['user_id'],
                'answer_scene_id' => $source['answer_scene_id'],
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
        $this->beginTransaction();
        try {
            $pool = $this->handleQuestionPool($source);
            $wrongQuestions = [];
            foreach ($wrongAnswerQuestionReports as $wrongAnswerQuestionReport) {
                $collect = $this->handleQuestionCollect(['item_id' => $wrongAnswerQuestionReport['item_id'], 'pool_id' => $pool['id']]);
                $wrongQuestions[] = [
                    'collect_id' => $collect['id'],
                    'user_id' => $source['user_id'],
                    'item_id' => $wrongAnswerQuestionReport['item_id'],
                    'question_id' => $wrongAnswerQuestionReport['question_id'],
                    'answer_scene_id' => $source['answer_scene_id'],
                    'answer_question_report_id' => $wrongAnswerQuestionReport['id'],
                    'submit_time' => time(),
                ];
            }
            $this->getWrongQuestionDao()->batchCreate($wrongQuestions);

            $this->getLogService()->info(
                'wrong_question',
                'create_wrong_question',
                '批量创建错题',
                $wrongQuestions
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->dispatchEvent('wrong_question.batch_create', $wrongQuestions, ['pool_id' => $pool['id']]);
    }

    public function createWrongQuestion($fields)
    {
        $wrongQuestionRequireFields = [
            'collect_id',
            'user_id',
            'question_id',
            'item_id',
            'answer_scene_id',
            'answer_question_report_id',
        ];
        if (!ArrayToolkit::requireds($fields, $wrongQuestionRequireFields)) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }

        $wrongQuestionRequireFields = ArrayToolkit::parts($fields, $wrongQuestionRequireFields);

        return  $this->getWrongQuestionDao()->create(array_merge($wrongQuestionRequireFields, ['submit_time' => time()]));
    }

    public function searchWrongQuestion($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getWrongQuestionDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countWrongQuestion($conditions)
    {
        return $this->getWrongQuestionDao()->count($conditions);
    }

    public function searchWrongBookPool($conditions, $orderBys, $start, $limit)
    {
        return $this->getWrongQuestionBookPoolDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countWrongBookPool($conditions)
    {
        return $this->getWrongQuestionBookPoolDao()->count($conditions);
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

        $this->beginTransaction();
        try {
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

    protected function handleQuestionCollect($fields)
    {
        $collectRequireFields = [
            'pool_id',
            'item_id',
        ];
        if (!ArrayToolkit::requireds($fields, $collectRequireFields)) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }

        $collect = $this->getWrongQuestionCollectDao()->getCollect($fields['pool_id'], $fields['item_id']);

        if (!$collect) {
            $collectFields = ArrayToolkit::parts($fields, $collectRequireFields);
            $collectFields['last_submit_time'] = time();
            $collect = $this->getWrongQuestionCollectDao()->create($collectFields);
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

        $pool = $this->getWrongQuestionBookPoolDao()->getPool($fields['user_id'], $fields['target_type'], $fields['target_id']);

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
