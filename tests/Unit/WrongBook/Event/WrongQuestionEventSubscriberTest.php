<?php

namespace Tests\Unit\WrongBook\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Biz\WrongBook\Event\WrongQuestionSubscriber;

class WrongQuestionEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $expected = [
            'answer.submitted' => 'onAnswerSubmitted',
            'wrong_question.batch_create' => 'onWrongQuestionBatchChanged',
            'wrong_question_pool.delete' => 'onWrongQuestionPoolDelete',
            'wrong_question.batch_delete' => 'onWrongQuestionBatchDelete',
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
        ];
        $this->assertEquals($expected, WrongQuestionSubscriber::getSubscribedEvents());
    }

    public function testOnWrongQuestionPoolDelete()
    {
        $created = $this->getWrongQuestionBookPoolDao()->create($this->mockPool());
        $this->createWrongQuestionCollect();
        $this->batchCreateWrongQuestion();
        $wrongPools = $this->getWrongQuestionBookPoolDao()->findPoolsByTargetIdAndTargetType(1, 'course');
        $wrongPoolIds = ArrayToolkit::column($wrongPools, 'id');
        $this->getWrongQuestionBookPoolDao()->deleteWrongPoolByTargetIdAndTargetType(1, 'course');
        $collecIds = $this->getWrongQuestionCollectDao()->getCollectIdsBYPoolIds($wrongPoolIds);
        $this->getWrongQuestionCollectDao()->deleteCollectByPoolIds($wrongPoolIds);
        $collecIds = ArrayToolkit::column($collecIds, 'id');
        $this->getWrongQuestionDao()->batchDelete(['collect_ids' => $collecIds]);
        $wrongPools = $this->getWrongQuestionBookPoolDao()->findPoolsByTargetIdAndTargetType(1, 'course');

        $this->assertEquals(0, count($wrongPools));
    }

    protected function batchCreateWrongQuestion()
    {
        $wrongQuestions = [
            [
                'collect_id' => 1,
                'user_id' => 1,
                'question_id' => 1,
                'item_id' => 1,
                'answer_scene_id' => 1,
                'testpaper_id' => 1,
                'answer_question_report_id' => 1,
                'submit_time' => time(),
            ],
            [
                'collect_id' => 1,
                'user_id' => 2,
                'question_id' => 1,
                'item_id' => 1,
                'answer_scene_id' => 2,
                'testpaper_id' => 1,
                'answer_question_report_id' => 1,
                'submit_time' => time(),
            ],
            [
                'collect_id' => 2,
                'user_id' => 2,
                'question_id' => 1,
                'item_id' => 2,
                'answer_scene_id' => 2,
                'testpaper_id' => 1,
                'answer_question_report_id' => 1,
                'submit_time' => time(),
            ],
        ];

        return $this->getWrongQuestionDao()->batchCreate($wrongQuestions);
    }

    protected function createWrongQuestionCollect()
    {
        $collectRequireFields = [
            'pool_id' => 1,
            'item_id' => 1,
            'last_submit_time' => time(),
        ];

        return $this->getWrongQuestionCollectDao()->create($collectRequireFields);
    }

    protected function mockPool($customFields = [])
    {
        return array_merge([
            'user_id' => 1,
            'item_num' => 1,
            'target_type' => 'course',
            'target_id' => 1,
        ], $customFields);
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
     * @return WrongQuestionDao
     */
    protected function getWrongQuestionDao()
    {
        return $this->createDao('WrongBook:WrongQuestionDao');
    }
}
