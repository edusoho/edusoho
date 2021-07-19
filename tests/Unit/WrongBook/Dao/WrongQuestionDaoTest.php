<?php

namespace Tests\Unit\WrongBook\Dao;

use Biz\BaseTestCase;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Dao\WrongQuestionDao;

class WrongQuestionDaoTest extends BaseTestCase
{
    public function testFindWrongQuestionBySceneIds()
    {
        $this->mockWrongQuestion();
        $wrongQuestions = $this->getWrongQuestionDao()->findWrongQuestionBySceneIds([1, 2]);
        $this->assertCount(3, $wrongQuestions);
    }

    public function testSearchWrongQuestionsWithDistinctUserId()
    {
        $this->mockWrongQuestion();
        $wrongQuestions = $this->getWrongQuestionDao()->searchWrongQuestionsWithDistinctUserId([
            'answer_scene_ids' => [1, 2],
            'item_id' => 1,
        ], [], 0, PHP_INT_MAX);

        $this->assertCount(2, $wrongQuestions);
    }

    public function testCountWrongQuestionsWithDistinctUserId()
    {
        $this->mockWrongQuestion();
        $wrongQuestions = $this->getWrongQuestionDao()->countWrongQuestionsWithDistinctUserId([
            'answer_scene_ids' => [1, 2],
            'item_id' => 1,
        ]);

        $this->assertEquals(2, $wrongQuestions);
    }

    public function testFindWrongQuestionsByUserIdsAndItemIdAndSceneIds()
    {
        $this->mockWrongQuestion();
        $wrongQuestions = $this->getWrongQuestionDao()->findWrongQuestionsByUserIdsAndItemIdAndSceneIds([1, 2], 1, [1, 2]);
        $this->assertCount(2, $wrongQuestions);
    }

    public function testFindWrongQuestionsByUserIdAndItemIdsAndSceneIds()
    {
        $this->mockWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionDao()->findWrongQuestionsByUserIdAndItemIdsAndSceneIds(1, [1], [1, 2]);
        $this->assertCount(1, $wrongQuestion);
    }

    public function testFindWrongQuestionsByUserIdAndSceneIds()
    {
        $this->mockWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionDao()->findWrongQuestionsByUserIdAndSceneIds(2, [1, 2]);
        $this->assertCount(2, $wrongQuestion);
    }

    public function testSearchWrongQuestionsWithCollect()
    {
        $this->mockWrongQuestion();
        $this->createWrongQuestionCollect();
        $wrongQuestion = $this->getWrongQuestionDao()->searchWrongQuestionsWithCollect([
            'pool_id' => 1,
        ], [], 0, PHP_INT_MAX, []);
        $this->assertCount(1, $wrongQuestion);
    }

    public function testSearchWrongQuestionsWithDistinctItem()
    {
        $this->mockWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionDao()->searchWrongQuestionsWithDistinctItem([
            'answer_scene_ids' => [1, 2],
        ], [], 0, PHP_INT_MAX, []);
        $this->assertCount(2, $wrongQuestion);
    }

    public function testCountWrongQuestionWithCollect()
    {
        $this->mockWrongQuestion();
        $this->createWrongQuestionCollect();
        $wrongQuestionCount = $this->getWrongQuestionDao()->countWrongQuestionWithCollect([
            'pool_id' => 1, ]);
        $this->assertEquals(1, $wrongQuestionCount);
    }

    public function testCountWrongQuestionsWithDistinctItem()
    {
        $this->mockWrongQuestion();
        $wrongQuestionCount = $this->getWrongQuestionDao()->countWrongQuestionsWithDistinctItem([
            'answer_scene_ids' => [1, 2],
        ]);
        $this->assertEquals(2, $wrongQuestionCount);
    }

    protected function mockWrongQuestion()
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
                'item_id' => 3,
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
