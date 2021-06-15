<?php

namespace Tests\Unit\WrongBook\Service;

use Biz\BaseTestCase;
use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Service\WrongQuestionService;

class WrongQuestionServiceTest extends BaseTestCase
{
    public function testCreateWrongQuestion()
    {
        $wrongQuestion = [
            'target_type' => 'course',
            'target_id' => 1,
            'item_id' => 1,
            'question_id' => 1,
            'answer_scene_id' => 1,
            'answer_question_report_id' => 1,
        ];
        $wrongQuestion = $this->getWrongQuestionService()->createWrongQuestion($wrongQuestion);

        $questionCollect = $this->getWrongQuestionCollectDao()->get($wrongQuestion['collect_id']);

        $this->assertNotEmpty($questionCollect);

        $questionPool = $this->getWrongQuestionBookPoolDao()->get($questionCollect['pool_id']);

        $this->assertNotEmpty($questionPool);
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->createService('WrongBook:WrongQuestionService');
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
