<?php

namespace Tests\Answer\Service;

use Tests\IntegrationTestCase;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class AnswerServiceTest extends IntegrationTestCase
{
    public function testStartAnswer()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'create',
                'returnValue' => ['answer_scene_id' => 1, 'assessment_id' => 1, 'user_id' => 1],
            ],
            [
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => [],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerSceneService', [
            [
                'functionName' => 'canStart',
                'returnValue' => true,
            ],
        ]);

        $answerRecord = $this->getAnswerService()->startAnswer(1, 1, 1);

        $this->assertEquals($answerRecord['answer_scene_id'], 1);
        $this->assertEquals($answerRecord['assessment_id'], 1);
        $this->assertEquals($answerRecord['user_id'], 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException
     * @expectedExceptionCode 50095202
     */
    public function testStartAnswer_whenCanStart_thenThrowException()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => [],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerSceneService', [
            [
                'functionName' => 'canStart',
                'returnValue' => false,
            ],
        ]);

        $this->getAnswerService()->startAnswer(1, 1, 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException
     * @expectedExceptionCode 50095207
     */
    public function testStartAnswer_whenCanRestart_thenThrowException()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerSceneService', [
            [
                'functionName' => 'canRestart',
                'returnValue' => false,
            ],
        ]);

        $this->getAnswerService()->startAnswer(1, 1, 1);
    }

    public function testPauseAnswer()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerService', [
            [
                'functionName' => 'saveAnswer',
                'returnValue' => [
                    'assessment_id' => '',
                    'answer_record_id' => '',
                    'used_time' => 3600,
                    'section_responses' => [],
                ],
            ],
            [
                'functionName' => 'pauseAnswer',
                'returnValue' => [
                    'id' => 1,
                    'status' => AnswerService::ANSWER_RECORD_STATUS_PAUSED,
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'update',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_PAUSED,
                ],
            ],
        ]);

        $answerRecord = $this->getAnswerService()->pauseAnswer(['answer_record_id' => 1]);
        $this->assertEquals($answerRecord['id'], 1);
        $this->assertEquals($answerRecord['status'], AnswerService::ANSWER_RECORD_STATUS_PAUSED);
    }

    public function testContinueAnswer()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_PAUSED,
                ],
            ],
            [
                'functionName' => 'update',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_DOING,
                ],
            ],
        ]);

        $answerRecord = $this->getAnswerService()->continueAnswer(1);

        $this->assertEquals($answerRecord['id'], 1);
        $this->assertEquals($answerRecord['status'], AnswerService::ANSWER_RECORD_STATUS_DOING);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerException
     * @expectedExceptionCode 40495203
     */
    public function testContinueAnswer_whenAnswerRecordMiss_thenThrowException()
    {
        $this->getAnswerService()->continueAnswer(1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerException
     * @expectedExceptionCode 50095205
     */
    public function testContinueAnswer_whenStatusNotPaused_thenThrowException()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED,
                ],
            ],
        ]);
        $this->getAnswerService()->continueAnswer(1);
    }

    public function testSaveAnswer()
    {
        $assessmentResponse = $this->mockAssessmentResponse();

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_DOING,
                    'assessment_id' => 1,
                ],
            ],
            [
                'functionName' => 'update',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_DOING,
                ],
            ],
        ]);

        $this->getAnswerService()->saveAnswer($assessmentResponse);

        $questionReports = $this->getAnswerQuestionReportDao()->findByAnswerRecordId(1);

        $this->assertEquals(count($questionReports), 2);
    }

    public function testSubmitAnswer()
    {
        $assessmentResponse = $this->mockAssessmentResponse();

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerSceneService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'need_score' => 1,
                ],
            ],
            [
                'functionName' => 'buildAnswerSceneReport',
                'returnValue' => [
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_DOING,
                    'assessment_id' => 1,
                ],
            ],
            [
                'functionName' => 'update',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED,
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerReportService', [
            [
                'functionName' => 'create',
                'returnValue' => [
                    'id' => 1,
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentService', [
            [
                'functionName' => 'review',
                'returnValue' => [
                   'id' => 1,
                   'total_score' => 2,
                   'score' => 2,
                   'section_reports' => [
                       [
                           'id' => 1,
                           'total_score' => 2,
                           'score' => 2,
                           'item_reports' => [
                               [
                                   'id' => 1,
                                   'total_score' => 2,
                                   'score' => 2,
                                   'question_count' => 1,
                                   'question_reports' => [
                                       [
                                           'id' => 1,
                                           'total_score' => 2,
                                           'score' => 2,
                                           'status' => 'right',
                                           'response' => ['A'],
                                       ],
                                   ],
                               ],
                           ],
                        ],
                        [
                           'id' => 2,
                           'total_score' => 2,
                           'score' => 2,
                           'item_reports' => [
                               [
                                   'id' => 2,
                                   'total_score' => 2,
                                   'score' => 2,
                                   'question_count' => 1,
                                   'question_reports' => [
                                       [
                                           'id' => 2,
                                           'total_score' => 2,
                                           'score' => 2,
                                           'status' => 'right',
                                           'response' => ['A'],
                                       ],
                                   ],
                               ],
                           ],
                       ],
                   ],
                ],
            ],
        ]);

        $answerRecord = $this->getAnswerService()->submitAnswer($assessmentResponse);
        $questionReports = $this->getAnswerQuestionReportDao()->findByAnswerRecordId(1);

        $this->assertEquals(count($questionReports), 2);
        $this->assertEquals($answerRecord['status'], AnswerService::ANSWER_RECORD_STATUS_FINISHED);
    }

    public function testReview()
    {
        $reviewReport = [
            'report_id' => 1,
            'grade' => 'excellent',
            'comment' => '总体评语',
            'question_reports' => [
                [
                    'id' => 1,
                    'score' => 2,
                    'comment' => '很好',
                ],
                [
                    'id' => 2,
                    'score' => 4,
                    'comment' => '很好1',
                ],
                [
                    'id' => 3,
                    'score' => 0,
                    'comment' => '很好2',
                ],
                [
                    'id' => 4,
                    'score' => 2,
                    'comment' => '很好3',
                ],
                [
                    'id' => 5,
                    'score' => 1,
                    'comment' => '很好4',
                ],
            ],
        ];

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_REVIEWING,
                    'assessment_id' => 1,
                ],
            ],
            [
                'functionName' => 'update',
                'returnValue' => [
                    'id' => '1',
                    'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED,
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerSceneService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'need_score' => 1,
                ],
            ],
            [
                'functionName' => 'buildAnswerSceneReport',
                'returnValue' => [
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Item:ItemService', [
            [
                'functionName' => 'findQuestionsByQuestionIds',
                'returnValue' => [
                    '1' => ['id' => 1, 'answer_mode' => 'single_choice'],
                    '2' => ['id' => 2, 'answer_mode' => 'rich_text'],
                    '3' => ['id' => 3, 'answer_mode' => 'single_choice'],
                    '4' => ['id' => 4, 'answer_mode' => 'single_choice'],
                    '5' => ['id' => 5, 'answer_mode' => 'single_choice'],
                ],
            ],
        ]);

        $this->getAnswerQuestionReportDao()->batchCreate([
            [
                'id' => '1',
                'identify' => '1_1',
                'answer_record_id' => 1,
                'assessment_id' => 1,
                'section_id' => 1,
                'item_id' => 1,
                'question_id' => 1,
                'score' => 0,
                'total_score' => 2,
                'response' => ['A'],
                'status' => 'no_answer',
            ],
            [
                'id' => '2',
                'identify' => '1_2',
                'answer_record_id' => 1,
                'assessment_id' => 1,
                'section_id' => 1,
                'item_id' => 2,
                'question_id' => 2,
                'score' => 0,
                'total_score' => 2,
                'response' => ['A'],
                'status' => 'reviewing',
            ],
            [
                'id' => '3',
                'identify' => '1_3',
                'answer_record_id' => 1,
                'assessment_id' => 1,
                'section_id' => 1,
                'item_id' => 3,
                'question_id' => 3,
                'score' => 0,
                'total_score' => 2,
                'response' => ['A'],
                'status' => 'reviewing',
            ],
            [
                'id' => '4',
                'identify' => '1_4',
                'answer_record_id' => 1,
                'assessment_id' => 1,
                'section_id' => 1,
                'item_id' => 3,
                'question_id' => 4,
                'score' => 0,
                'total_score' => 2,
                'response' => [],
                'status' => 'reviewing',
            ],
            [
                'id' => '5',
                'identify' => '1_5',
                'answer_record_id' => 1,
                'assessment_id' => 1,
                'section_id' => 1,
                'item_id' => 3,
                'question_id' => 5,
                'score' => 0,
                'total_score' => 2,
                'response' => ['A'],
                'status' => 'reviewing',
            ],
        ]);

        $this->getAnswerReportDao()->create([
            'id' => 1,
            'assessment_id' => 1,
            'answer_record_id' => 1,
        ]);

        $answerReport = $this->getAnswerService()->review($reviewReport);

        $aswerQuestionRerport = ArrayToolkit::index($this->getAnswerQuestionReportDao()->search(['ids' => [1, 2, 3, 4, 5]], [], 0, 5), 'id');
        $this->assertEquals($aswerQuestionRerport['1']['status'], 'no_answer');
        $this->assertEquals($aswerQuestionRerport['2']['status'], 'right');
        $this->assertEquals($aswerQuestionRerport['2']['score'], 2);
        $this->assertEquals($aswerQuestionRerport['3']['status'], 'wrong');
        $this->assertEquals($aswerQuestionRerport['4']['status'], 'no_answer');
        $this->assertEquals($aswerQuestionRerport['4']['score'], 0);
        $this->assertEquals($aswerQuestionRerport['5']['status'], 'part_right');
        $this->assertEquals($answerReport['right_rate'], 20);
        $this->assertEquals($answerReport['objective_score'], 1);
        $this->assertEquals($answerReport['subjective_score'], 2);
        $this->assertEquals($answerReport['right_question_count'], 1);
        $this->assertEquals($answerReport['score'], 3);
        $this->assertEquals($answerReport['grade'], 'excellent');
        $this->assertEquals($answerReport['comment'], '总体评语');
    }

    public function testGetAssessmentResponseByAnswerRecordId()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => '1',
                    'assessment_id' => 1,
                    'used_time' => 10,
                ],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerQuestionReportService', [
            [
                'functionName' => 'findByAnswerRecordId',
                'returnValue' => [
                    [
                        'id' => 1,
                        'identify' => '1_1',
                        'answer_record_id' => 1,
                        'assessment_id' => 1,
                        'section_id' => 1,
                        'item_id' => 1,
                        'question_id' => 1,
                        'score' => 1,
                        'total_score' => 1,
                        'response' => [],
                        'status' => 'reviewing',
                    ],
                    [
                        'id' => 2,
                        'identify' => '1_1',
                        'answer_record_id' => 1,
                        'assessment_id' => 1,
                        'section_id' => 1,
                        'item_id' => 2,
                        'question_id' => 2,
                        'score' => 1,
                        'total_score' => 1,
                        'response' => [],
                        'status' => 'reviewing',
                    ],
                ],
            ],
        ]);

        $assessmentResponse = $this->getAnswerService()->getAssessmentResponseByAnswerRecordId(1);

        $this->assertEquals($assessmentResponse['section_responses'][0]['item_responses'][0]['question_responses'][0]['question_id'], 1);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Answer\Exception\AnswerException
     * @expectedExceptionCode 40495203
     */
    public function testGetAssessmentResponseByAnswerRecordId_whenAnswerRecordIdMiss_thenThrowException()
    {
        $this->getAnswerService()->getAssessmentResponseByAnswerRecordId(1);
    }

    protected function mockAssessmentResponse()
    {
        return [
            'assessment_id' => 1,
            'answer_record_id' => 1,
            'used_time' => 60,
            'section_responses' => [
                [
                    'section_id' => 1,
                    'item_responses' => [
                        [
                            'item_id' => 1,
                            'question_responses' => [
                                [
                                    'question_id' => 1,
                                    'response' => ['A'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'section_id' => 2,
                    'item_responses' => [
                        [
                            'item_id' => 2,
                            'question_responses' => [
                                [
                                    'question_id' => 2,
                                    'response' => ['A'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getAnswerService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerQuestionReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerQuestionReportDao');
    }

    protected function getAnswerReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerReportDao');
    }
}
