<?php

namespace Tests\Unit\WrongBook\Service;

use Biz\BaseTestCase;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Dao\WrongQuestionDao;
use Biz\WrongBook\Service\WrongQuestionService;

class WrongQuestionServiceTest extends BaseTestCase
{
    public function testCreateWrongQuestion()
    {
        $wrongQuestion = [
            'item_id' => 1,
            'question_id' => 1,
            'answer_question_report_id' => 1,
        ];
        $source = [
            'user_id' => 1,
            'answer_scene_id' => 1,
            'target_type' => 'course',
            'testpaper_id' => 1,
            'target_id' => 1,
        ];
        $wrongQuestion = $this->getWrongQuestionService()->buildWrongQuestion($wrongQuestion, $source);

        $questionCollect = $this->getWrongQuestionCollectDao()->get($wrongQuestion['collect_id']);

        $this->assertNotEmpty($questionCollect);

        $questionPool = $this->getWrongQuestionBookPoolDao()->get($questionCollect['pool_id']);

        $this->assertNotEmpty($questionPool);
    }

    public function testGetPool()
    {
        $created = $this->getWrongQuestionBookPoolDao()->create($this->mockPool());
        $get = $this->getWrongQuestionBookPoolDao()->get($created['id']);
        self::assertEquals($created, $get);
    }

    public function testGetPoolBySceneId()
    {
        $created = $this->getWrongQuestionBookPoolDao()->create($this->mockPool(['scene_id' => 3]));
        $get = $this->getWrongQuestionBookPoolDao()->get($created['id']);
        self::assertEquals(3, $get['scene_id']);
    }

    public function testBatchBuildWrongQuestion()
    {
        $wrongAnswerQuestionReports = [
            [
                'id' => 2,
                'item_id' => 2,
                'question_id' => 2,
            ],
            [
                'id' => 3,
                'item_id' => 3,
                'question_id' => 3,
            ],
        ];
        $source = [
            'user_id' => 4,
            'answer_scene_id' => 4,
            'target_type' => 'classroom',
            'testpaper_id' => 1,
            'target_id' => 4,
        ];
        $this->getWrongQuestionService()->batchBuildWrongQuestion($wrongAnswerQuestionReports, $source);
        $wrongQuestions = $this->getWrongQuestionDao()->search(['answer_scene_id' => 4], [], 0, PHP_INT_MAX);

        $this->assertCount(2, $wrongQuestions);
    }

    public function testGetWrongBookPoolByFieldsGroupByTargetType()
    {
        $pools = $this->mockPools();
        foreach ($pools as $key => $pool) {
            $this->getWrongQuestionBookPoolDao()->create($pool);
        }
        $wrongPools = $this->getWrongQuestionService()->getWrongBookPoolByFieldsGroupByTargetType(['user_id' => 2]);
        $this->assertCount(2, $wrongPools);
    }

    public function testCountWrongBookPool()
    {
        $pools = $this->mockPools();
        foreach ($pools as $key => $pool) {
            $this->getWrongQuestionBookPoolDao()->create($pool);
        }
        $wrongPoolsNum = $this->getWrongQuestionService()->countWrongBookPool(['user_id' => 2]);
        $this->assertEquals('3', $wrongPoolsNum);
    }

    public function testSearchWrongQuestion()
    {
        $this->createWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionService()->searchWrongQuestion([
            'user_id' => 1,
            'item_id' => 1,
        ], [], 0, PHP_INT_MAX);

        $this->assertCount(1, $wrongQuestion);
    }

    public function testCountWrongQuestion()
    {
        $this->createWrongQuestion();
        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestion([
            'user_id' => 1,
            'item_id' => 1,
        ]);
        $this->assertEquals(1, $wrongQuestionCount);
    }

    public function testSearchWrongQuestionsWithDistinctUserId()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestionWithDistinctUserId = $this->getWrongQuestionService()->searchWrongQuestionsWithDistinctUserId([
            'answer_scene_ids' => [1, 2],
            'item_id' => 1,
        ], [], 0, PHP_INT_MAX);

        $this->assertCount(2, $wrongQuestionWithDistinctUserId);
    }

    public function testCountWrongQuestionsWithDistinctUserId()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestionWithDistinctUserId = $this->getWrongQuestionService()->countWrongQuestionsWithDistinctUserId([
            'answer_scene_ids' => [1, 2],
            'item_id' => 1, ]);

        $this->assertEquals(2, $wrongQuestionWithDistinctUserId);
    }

    public function testFindWrongQuestionsByUserIdsAndItemIdAndSceneIds()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionService()->findWrongQuestionsByUserIdsAndItemIdAndSceneIds([1, 2], 1, [1, 2]);
        $this->assertCount(2, $wrongQuestion);
    }

    public function findWrongQuestionsByUserIdAndItemIdAndSceneIds()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionService()->findWrongQuestionsByUserIdAndItemIdAndSceneIds(1, 1, [1, 2]);
        $this->assertCount(1, $wrongQuestion);
    }

    public function findWrongQuestionsByUserIdAndSceneIds()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionService()->findWrongQuestionsByUserIdAndSceneIds(2, [1, 2]);
        $this->assertCount(2, $wrongQuestion);
    }

    public function testSearchWrongQuestionsWithCollect()
    {
        $this->batchCreateWrongQuestion();
        $this->createWrongQuestionCollect();
        $wrongQuestion = $this->getWrongQuestionService()->searchWrongQuestionsWithCollect([
            'pool_id' => 1,
        ], [], 0, PHP_INT_MAX);
        $this->assertCount(1, $wrongQuestion);
    }

    public function testCountWrongQuestionWithCollect()
    {
        $this->batchCreateWrongQuestion();
        $this->createWrongQuestionCollect();
        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestionWithCollect([
            'pool_id' => 1, ]);
        $this->assertEquals(1, $wrongQuestionCount);
    }

    public function testSearchWrongQuestionsWithDistinctItem()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestion = $this->getWrongQuestionService()->searchWrongQuestionsWithDistinctItem([
            'answer_scene_ids' => [1, 2],
        ], [], 0, PHP_INT_MAX);
        $this->assertCount(2, $wrongQuestion);
    }

    public function testCountWrongQuestionsWithDistinctItem()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestionCount = $this->getWrongQuestionService()->countWrongQuestionsWithDistinctItem([
            'answer_scene_ids' => [1, 2],
        ]);
        $this->assertEquals(2, $wrongQuestionCount);
    }

    public function testSearchWrongQuestionCollect()
    {
        $this->createWrongQuestionCollect();
        $wrongQuestionCollect = $this->getWrongQuestionService()->searchWrongQuestionCollect([
            'item_id' => 1,
        ], [], 0, PHP_INT_MAX);
        $this->assertCount(1, $wrongQuestionCollect);
    }

    public function testSearchWrongBookPool()
    {
        $this->getWrongQuestionBookPoolDao()->create($this->mockPool());
        $wrongQuestionCollect = $this->getWrongQuestionService()->searchWrongBookPool([
            'target_id' => 1,
        ], [], 0, PHP_INT_MAX);
        $this->assertCount(1, $wrongQuestionCollect);
    }

    public function testDeleteWrongQuestion()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestionsOld = $this->getWrongQuestionDao()->search(['item_id' => 1], [], 0, PHP_INT_MAX);
        $this->getWrongQuestionService()->deleteWrongQuestion($wrongQuestionsOld[0]['id']);
        $wrongQuestionsNew = $this->getWrongQuestionDao()->search(['item_id' => 1], [], 0, PHP_INT_MAX);
        $this->assertCount(count($wrongQuestionsOld) - 1, $wrongQuestionsNew);
    }

    public function testFindWrongQuestionBySceneIds()
    {
        $this->batchCreateWrongQuestion();
        $wrongQuestions = $this->getWrongQuestionService()->findWrongQuestionBySceneIds([1, 2]);
        $this->assertCount(3, $wrongQuestions);
    }

    protected function createWrongQuestion($wrongQuestionFields = [])
    {
        $wrongQuestion = [
            'collect_id' => 1,
            'user_id' => 1,
            'question_id' => 1,
            'item_id' => 1,
            'answer_scene_id' => 1,
            'testpaper_id' => 1,
            'answer_question_report_id' => 1,
            'submit_time' => time(),
        ];
        $wrongQuestion = array_merge($wrongQuestion, $wrongQuestionFields);

        return $this->getWrongQuestionDao()->create($wrongQuestion);
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

    protected function mockPools()
    {
        return [
            [
                'user_id' => 2,
                'item_num' => 1,
                'target_type' => 'course',
                'target_id' => 1,
                'created_time' => 0,
                'updated_time' => 0,
            ],
            [
                'user_id' => 2,
                'item_num' => 2,
                'target_type' => 'course',
                'target_id' => 1,
                'created_time' => 0,
                'updated_time' => 0,
            ],
            [
                'user_id' => 2,
                'item_num' => 1,
                'target_type' => 'classroom',
                'target_id' => 1,
                'created_time' => 0,
                'updated_time' => 0,
            ],
            [
                'user_id' => 1,
                'item_num' => 1,
                'target_type' => 'classroom',
                'target_id' => 1,
                'created_time' => 0,
                'updated_time' => 0,
            ],
        ];
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->createService('WrongBook:WrongQuestionService');
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
}
