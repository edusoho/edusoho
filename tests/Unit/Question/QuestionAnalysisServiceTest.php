<?php

namespace Tests\Unit\OpenCourse;

use Biz\BaseTestCase;

class QuestionAnalysisServiceTest extends BaseTestCase
{
    public function testWaveCount()
    {
        $fields = array(
            'targetId' => 1,
            'targetType' => 'testpaper',
            'activityId' => 1,
            'questionId' => 5,
            'choiceIndex' => 1,
        );
        $analysis = $this->getQuestionAnalysisDao()->create($fields);

        $this->getQuestionAnalysisService()->waveCount($analysis['id'], array('totalAnswerCount' => 1));

        $result = $this->getQuestionAnalysisDao()->get($analysis['id']);

        $this->assertEquals($analysis['totalAnswerCount'] + 1, $result['totalAnswerCount']);
    }

    public function testSearchAnalysis()
    {
        $fields = array(
            'targetId' => 1,
            'targetType' => 'testpaper',
            'activityId' => 1,
            'questionId' => 5,
            'choiceIndex' => 1,
        );
        $this->getQuestionAnalysisDao()->create($fields);

        $fields = array(
            'targetId' => 2,
            'targetType' => 'homework',
            'activityId' => 2,
            'questionId' => 5,
            'choiceIndex' => 1,
        );
        $this->getQuestionAnalysisDao()->create($fields);

        $fields = array(
            'targetId' => 2,
            'targetType' => 'homework',
            'activityId' => 2,
            'questionId' => 5,
            'choiceIndex' => 2,
        );
        $this->getQuestionAnalysisDao()->create($fields);

        $conditions = array('activityId' => 2);
        $result = $this->getQuestionAnalysisService()->searchAnalysis($conditions, array('createdTime' => 'ASC'), 0, 5);

        $this->assertEquals(2, count($result));
    }

    public function testCountAnalysis()
    {
        $fields = array(
            'targetId' => 1,
            'targetType' => 'testpaper',
            'activityId' => 1,
            'questionId' => 5,
            'choiceIndex' => 1,
        );
        $this->getQuestionAnalysisDao()->create($fields);

        $fields = array(
            'targetId' => 2,
            'targetType' => 'homework',
            'activityId' => 2,
            'questionId' => 5,
            'choiceIndex' => 1,
        );
        $this->getQuestionAnalysisDao()->create($fields);

        $fields = array(
            'targetId' => 2,
            'targetType' => 'homework',
            'activityId' => 2,
            'questionId' => 5,
            'choiceIndex' => 2,
        );
        $this->getQuestionAnalysisDao()->create($fields);

        $conditions = array('activityId' => 2);
        $count = $this->getQuestionAnalysisService()->countAnalysis($conditions);

        $this->assertEquals(2, $count);
    }

    public function testBatchCreate()
    {
        $rows = array();
        for ($i = 0; $i < 4; ++$i) {
            $fields = array(
                'targetId' => 1,
                'targetType' => 'testpaper',
                'activityId' => 2,
                'questionId' => 5,
                'choiceIndex' => $i,
            );

            $rows[] = $fields;
        }

        $this->getQuestionAnalysisService()->batchCreate($rows);

        $count = $this->getQuestionAnalysisService()->countAnalysis(array('targetId' => 1, 'targetType' => 'testpaper', 'activityId' => 2));

        $this->assertEquals(4, $count);
    }

    protected function getQuestionAnalysisService()
    {
        return $this->createService('Question:QuestionAnalysisService');
    }

    protected function getQuestionAnalysisDao()
    {
        return $this->createDao('Question:QuestionAnalysisDao');
    }
}
