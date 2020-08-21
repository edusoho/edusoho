<?php

namespace Tests\Answer\Service;

use Tests\IntegrationTestCase;

class AnswerReportServiceTest extends IntegrationTestCase
{
    public function testGet()
    {
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentService', [
            [
                'functionName' => 'findAssessmentQuestions',
                'returnValue' => [
                    ['item_id' => 1, 'question_id' => '1', 'score' => '2', 'section_id' => '1', 'seq' => 1],
                    ['item_id' => 2, 'question_id' => '2', 'score' => '2', 'section_id' => '1', 'seq' => 2],
                    ['item_id' => 3, 'question_id' => '3', 'score' => '2', 'section_id' => '1', 'seq' => 3],
                    ['item_id' => 4, 'question_id' => '4', 'score' => '2', 'section_id' => '1', 'seq' => 4],
                    ['item_id' => 5, 'question_id' => '5', 'score' => '2', 'section_id' => '1', 'seq' => 5],
                ],
            ]
        ]);

        $answerReport = $this->fakeAnswerReport();

        $testAnswerReport = $this->getAnswerReportService()->get(1);
        $this->assertEquals($answerReport['id'], $testAnswerReport['id']);
        $this->assertEquals($testAnswerReport['section_reports'][0]['total_score'], 10);
        $this->assertEquals($testAnswerReport['section_reports'][0]['score'], 0);
        $this->assertEquals($testAnswerReport['section_reports'][0]['question_count'], 5);
        $this->assertEquals($testAnswerReport['section_reports'][0]['right_question_num'], 1);
        $this->assertEquals($testAnswerReport['section_reports'][0]['wrong_question_num'], 0);
        $this->assertEquals($testAnswerReport['section_reports'][0]['reviewing_question_num'], 0);
        $this->assertEquals($testAnswerReport['section_reports'][0]['no_answer_question_num'], 3);
        $this->assertEquals($testAnswerReport['section_reports'][0]['part_right_question_num'], 1);
        $this->assertEquals($testAnswerReport['section_reports'][0]['item_reports'][2]['right_question_num'], 1);
    }

    public function testUpdate()
    {
        $answerReport = $this->fakeAnswerReport();
        
        $time = time();
        $testAnswerReport = $this->getAnswerReportService()->update(1, [
            'score' => 4,
            'right_rate' => 40,
            'right_question_count' => 2,
            'review_user_id' => 1,
            'review_time' => $time,
            'grade' => 'excellent',
            'comment' => '总体评语',
        ]);

        $this->assertEquals($testAnswerReport['score'], 4);
        $this->assertEquals($testAnswerReport['right_rate'], 40);
        $this->assertEquals($testAnswerReport['right_question_count'], 2);
        $this->assertEquals($testAnswerReport['review_user_id'], 1);
        $this->assertEquals($testAnswerReport['review_time'], $time);
        $this->assertEquals($testAnswerReport['grade'], 'excellent');
        $this->assertEquals($testAnswerReport['comment'], '总体评语');
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerReportException
     * @expectedExceptionCode 40495206
     */
    public function testUpdate_whenIdMiss_thenThrowException()
    {
        $this->getAnswerReportService()->update(1, []);
    }

    public function testCreate()
    {
        $this->fakeAssessment();
        $this->fakeAnswerRecord();

        $answerReport = $this->getAnswerReportService()->create([
            'user_id' => 1,
            'assessment_id' => 1,
            'answer_record_id' => 1,
            'score' => 3,
            'total_score' => 4,
            'right_rate' => 75,
            'right_question_count' => 2,
            'comment' => '总体评语',
            'grade' => 'excellent',
        ]);

        $this->assertEquals($answerReport['assessment_id'], 1);
        $this->assertEquals($answerReport['answer_record_id'], 1);
        $this->assertEquals($answerReport['score'], 3);
        $this->assertEquals($answerReport['total_score'], 4);
        $this->assertEquals($answerReport['right_rate'], 75);
        $this->assertEquals($answerReport['right_question_count'], 2);
        $this->assertEquals($answerReport['comment'], '总体评语');
        $this->assertEquals($answerReport['answer_scene_id'], 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException
     * @expectedExceptionCode 40495101
     */
    public function testCreate_whenAssessmentNotFound_thenThrowException()
    {
        $this->fakeAssessment();
        $this->fakeAnswerRecord();

        $this->getAnswerReportService()->create([
            'user_id' => 2,
            'assessment_id' => 2,
            'answer_record_id' => 1,
            'score' => 3,
            'total_score' => 4,
            'right_rate' => 75,
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerException
     * @expectedExceptionCode 40495203
     */
    public function testCreate_whenAnswerRecordNotFound_thenThrowException()
    {
        $this->fakeAssessment();
        $this->fakeAnswerRecord();

        $this->getAnswerReportService()->create([
            'user_id' => 1,
            'assessment_id' => 1,
            'answer_record_id' => 2,
            'score' => 3,
            'total_score' => 4,
            'right_rate' => 75,
        ]);
    }

    public function testGetSimple()
    {
        $answerReport = $this->fakeAnswerReport();

        $testAnswerReport = $this->getAnswerReportService()->getSimple(1);

        $this->assertEquals($answerReport['id'], $testAnswerReport['id']);
    }

    public function testFindByAnswerSceneId()
    {
        $this->fakeAnswerReport();
        $answerReports = $this->getAnswerReportService()->findByAnswerSceneId(1);
        $this->assertEquals(count($answerReports), 1);
    }

    public function testSearch()
    {
        $this->fakeAnswerReport();
        $answerReports = $this->getAnswerReportService()->search(['answer_scene_id' => 1], [], 0, 1);
        $this->assertEquals(count($answerReports), 1);
    }


    public function testCount()
    {
        $this->fakeAnswerReport();
        $answerReports = $this->getAnswerReportService()->count(['answer_scene_id' => 1]);
        $this->assertEquals(count($answerReports), 1);
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

    public function fakeAnswerReport()
    {
        $answerReport = $this->getAnswerReportDao()->create([
            'id' => 1,
            'assessment_id' => 1,
            'answer_record_id' => 1,
            'answer_scene_id' => 1,
        ]);

        $answerQuestionReports = [
            [
                'id' => 1,
                'identify' => '1_1',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '1',
                'question_id' => '1',
                'status' => 'part_right',
                'response' => ['A'],
            ],
            [
                'id' => 2,
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'status' => 'wrong',
                'response' => ['A'],
            ],
            [
                'id' => 3,
                'identify' => '1_3',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'status' => 'reviewing',
                'response' => ['A'],
            ],
            [
                'id' => 4,
                'identify' => '1_4',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'status' => 'no_answer',
                'response' => ['A'],
            ],
            [
                'id' => 5,
                'identify' => '1_5',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'status' => 'right',
                'response' => ['A'],
            ],
        ];

        $this->getAnswerQuestionReportDao()->batchCreate($answerQuestionReports);

        return $answerReport;
    }

    protected function getAssessmentDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentDao');
    }

    protected function getAnswerRecordDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerRecordDao');
    }

    protected function getAnswerQuestionReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerQuestionReportDao');
    }

    public function getAnswerReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerReportDao');
    }

    public function getAnswerReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerReportService');
    }
}
