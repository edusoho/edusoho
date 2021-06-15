<?php

namespace Biz\WrongBook\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\LogService;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\WrongBook\WrongBookException;

class WrongQuestionServiceImpl extends BaseService implements WrongQuestionService
{
    public function createWrongQuestion($wrongQuestion)
    {
        $this->filterWrongQuestionFields($wrongQuestion);

        $this->beginTransaction();

        try {
            $wrongQuestion = $this->getWrongQuestionDao()->create($wrongQuestion);

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

        return $wrongQuestion;
    }

    public function searchWrongQuestion($conditions, $orderBys, $start, $limit)
    {
        return $this->getWrongQuestionDao()->search($conditions, $orderBys, $start, $limit);
    }

    private function filterWrongQuestionFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, [
            'item_id',
            'user_id',
            'answer_question_report_id',
            'target_type',
            'target_id',
            'sub_target_id',
            'source',
            'event_id',
            'error_time',
        ])) {
            throw WrongBookException::WRONG_QUESTION_DATA_FIELDS_MISSING();
        }
    }

    /**
     * @return WrongQuestionDao
     */
    protected function getWrongQuestionDao()
    {
        return $this->createDao(' WrongBook:WrongQuestionDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
