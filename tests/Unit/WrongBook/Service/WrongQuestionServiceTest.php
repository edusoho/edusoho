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
            'target_id' => 1,
        ];
        $wrongQuestion = $this->getWrongQuestionService()->buildWrongQuestion($wrongQuestion, $source);

        $questionCollect = $this->getWrongQuestionCollectDao()->get($wrongQuestion['collect_id']);

        $this->assertNotEmpty($questionCollect);

        $questionPool = $this->getWrongQuestionBookPoolDao()->get($questionCollect['pool_id']);

        $this->assertNotEmpty($questionPool);
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
            'target_id' => 4,
        ];
        $this->getWrongQuestionService()->batchBuildWrongQuestion($wrongAnswerQuestionReports, $source);
        $wrongQuestions = $this->getWrongQuestionDao()->search(['answer_scene_id' => 4], [], 0, PHP_INT_MAX);

        $this->assertCount(2, $wrongQuestions);
    }

    public function testGetWrongBookPoolByFieldsGroupByTargetType()
    {
        $pools = [
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
        foreach ($pools as $key => $pool) {
            $this->getWrongQuestionBookPoolDao()->create($pool);
        }
        $wrongPools = $this->getWrongQuestionService()->getWrongBookPoolByFieldsGroupByTargetType(['user_id' => 2]);
        $this->assertCount(2, $wrongPools);
    }

    public function testCountWrongBookPool()
    {
        $pools = [
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
        foreach ($pools as $key => $pool) {
            $this->getWrongQuestionBookPoolDao()->create($pool);
        }
        $wrongPoolsNum = $this->getWrongQuestionService()->countWrongBookPool(['user_id' => 2]);
        $this->assertEquals('3', $wrongPoolsNum);
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
