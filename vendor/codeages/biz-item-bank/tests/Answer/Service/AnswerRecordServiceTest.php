<?php

namespace Tests\Answer\Service;

use Tests\IntegrationTestCase;

class AnswerRecordServiceTest extends IntegrationTestCase
{
    public function testCreate()
    {
        $this->fakeAnswerScene();
        $this->fakeAssessment();

        $answerRecord = [
            'answer_scene_id' => 1,
            'assessment_id' => 1,
            'user_id' => 1,
        ];

        $answerRecord = $this->getAnswerRecordService()->create($answerRecord);

        $this->assertEquals($answerRecord['answer_scene_id'], 1);
        $this->assertEquals($answerRecord['assessment_id'], 1);
        $this->assertEquals($answerRecord['user_id'], 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testCreate_whenParamsMiss_thenThrowException()
    {
        $answerRecord = [
            'answer_scene_id' => -1,
            'assessment_id' => -1,
            'user_id' => -1,
        ];

        $this->getAnswerRecordService()->create($answerRecord);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException
     * @expectedExceptionCode 40495201
     */
    public function testCreate_whenAnswerSceneNotFound_thenThrowException()
    {
        $this->fakeAssessment();

        $answerRecord = [
            'answer_scene_id' => 1,
            'assessment_id' => 1,
            'user_id' => 1,
        ];

        $answerRecord = $this->getAnswerRecordService()->create($answerRecord);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException
     * @expectedExceptionCode 40495101
     */
    public function testCreate_whenAssessmentNotFound_thenThrowException()
    {
        $this->fakeAnswerScene();

        $answerRecord = [
            'answer_scene_id' => 1,
            'assessment_id' => 1,
            'user_id' => 1,
        ];

        $answerRecord = $this->getAnswerRecordService()->create($answerRecord);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException
     * @expectedExceptionCode 50095103
     */
    public function testCreate_whenAssessmentNotOpen_thenThrowException()
    {
        $this->fakeAnswerScene();

        $this->getAssessmentDao()->create([
            'id' => 1,
            'name' => '测试',
            'status' => 'draft',
        ]);

        $answerRecord = [
            'answer_scene_id' => 1,
            'assessment_id' => 1,
            'user_id' => 1,
        ];

        $answerRecord = $this->getAnswerRecordService()->create($answerRecord);
    }

    public function testUpdate()
    {
        $this->fakeAnswerRecord();

        $time = time();
        $answerRecord = $this->getAnswerRecordService()->update(1, [
            'status' => 'reviewing',
            'used_time' => 600,
            'answer_report_id' => 1,
            'end_time' => $time,
        ]);

        $this->assertEquals($answerRecord['status'], 'reviewing');
        $this->assertEquals($answerRecord['used_time'], 600);
        $this->assertEquals($answerRecord['answer_report_id'], 1);
        $this->assertEquals($answerRecord['end_time'], time());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testUpdate_whenParamsMiss_thenThrowException()
    {
        $this->fakeAnswerRecord();
        $this->getAnswerRecordService()->update(1, [
            'status' => '3333',
            'used_time' => 'dddd',
            'answer_report_id' => 'ddd',
            'end_time' => 'ddd',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerException
     * @expectedExceptionCode 40495203
     */
    public function testUpdate_whenIdMiss_thenThrowException()
    {
        $this->getAnswerRecordService()->update(1, [
            'status' => 'reviewing',
            'used_time' => 600,
            'answer_report_id' => 1,
            'end_time' => time(),
        ]);
    }

    public function testGet()
    {
        $answerRecord = $this->fakeAnswerRecord();

        $testAnswerRecord = $this->getAnswerRecordService()->get(1);

        $this->assertEquals($answerRecord['status'], $testAnswerRecord['status']);
        $this->assertEquals($answerRecord['answer_scene_id'], $testAnswerRecord['answer_scene_id']);
        $this->assertEquals($answerRecord['assessment_id'], $testAnswerRecord['assessment_id']);
        $this->assertEquals($answerRecord['user_id'], $testAnswerRecord['user_id']);
    }

    public function testgetLatestAnswerRecordByAnswerSceneIdAndUserId()
    {
        $answerRecord = $this->fakeAnswerRecord();

        $testAnswerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId(1, 1);

        $this->assertEquals($answerRecord['status'], $testAnswerRecord['status']);
        $this->assertEquals($answerRecord['answer_scene_id'], $testAnswerRecord['answer_scene_id']);
        $this->assertEquals($answerRecord['assessment_id'], $testAnswerRecord['assessment_id']);
        $this->assertEquals($answerRecord['user_id'], $testAnswerRecord['user_id']);
    }

    public function testSearch()
    {
        $this->fakeAnswerRecord();

        $answerRecords = $this->getAnswerRecordService()->search([], [], 0, 1);

        $this->assertEquals(count($answerRecords), 1);
    }

    public function testCount()
    {
        $this->fakeAnswerRecord();

        $count = $this->getAnswerRecordService()->count([]);

        $this->assertEquals($count, 1);
    }

    protected function fakeAnswerScene()
    {
        return $this->getAnswerSceneDao()->create([
            'id' => 1,
            'name' => '测试场次',
        ]);
    }

    protected function fakeAssessment()
    {
        return $this->getAssessmentDao()->create([
            'id' => 1,
            'bank_id' => 1,
            'name' => '数学期末试卷',
            'status' => 'open',
            'description' => '描述',
        ]);
    }

    protected function fakeAnswerRecord()
    {
        return $this->getAnswerRecordDao()->create([
            'id' => 1,
            'answer_scene_id' => 1,
            'assessment_id' => 1,
            'user_id' => 1,
        ]);
    }

    protected function getAnswerSceneDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerSceneDao');
    }

    protected function getAssessmentDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentDao');
    }

    protected function getAnswerRecordDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerRecordDao');
    }

    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }
}
