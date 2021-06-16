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
            'answer_scene_id' => 1,
            'answer_question_report_id' => 1,
        ];
        $source = [
            'user_id' => 1,
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
                'id' => 1,
                'item_id' => 1,
                'question_id' => 1,
                'answer_scene_id' => 1,
            ],
            [
                'id' => 2,
                'item_id' => 2,
                'question_id' => 2,
                'answer_scene_id' => 1,
            ],
        ];
        $source = [
            'user_id' => 1,
            'target_type' => 'course',
            'target_id' => 1,
        ];
        $this->getWrongQuestionService()->batchBuildWrongQuestion($wrongAnswerQuestionReports, $source);
        $wrongQuestions = $this->getWrongQuestionDao()->search(['answer_scene_id' => 1], [], 0, PHP_INT_MAX);

        $this->assertCount(2, $wrongQuestions);
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
