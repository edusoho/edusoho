<?php

namespace Tests\Answer\Service;

use Tests\IntegrationTestCase;

class AnswerQuestionReportServiceTest extends IntegrationTestCase
{
    public function testFindByIds()
    {
        $this->getAnswerQuestionReportDao()->batchCreate([
            [
                'id' => '1',
                'identify' => '1_1',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ],
            [
                'id' => '2',
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ],
        ]);

        $report = $this->getAnswerQuestionReportService()->findByIds([1, 2]);
        $this->assertCount(2, $report);
    }

    public function testBatchCreate()
    {
        $answerQuestionReports = [
            [
                'identify' => '1_1',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '1',
                'question_id' => '1',
                'response' => ['A'],
            ],
            [
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ],
        ];

        $this->getAnswerQuestionReportService()->batchCreate($answerQuestionReports);

        $count = $this->getAnswerQuestionReportDao()->count([]);

        $this->assertEquals($count, 2);
    }

    public function testCount()
    {
        $this->getAnswerQuestionReportDao()->create(
            [
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ]
        );

        $count = $this->getAnswerQuestionReportService()->count([]);

        $this->assertEquals($count, 1);
    }

    public function testBatchUpdate()
    {
        $this->getAnswerQuestionReportDao()->batchCreate([
            [
                'id' => '1',
                'identify' => '1_1',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ],
            [
                'id' => '2',
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ]
        ]);

        $this->getAnswerQuestionReportService()->batchUpdate([
            ['identify' => '1_1', 'answer_record_id' => 1],
            ['identify' => '1_2', 'answer_record_id' => 1],
        ]);
        
        $result = $this->getAnswerQuestionReportDao()->findByAnswerRecordId(1);

        $this->assertEquals(count($result), 2);
    }

    public function testFindByAnswerRecordId()
    {
        $this->getAnswerQuestionReportDao()->batchCreate([
            [
                'id' => '1',
                'identify' => '1_1',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ],
            [
                'id' => '2',
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ]
        ]);

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId(1);

        $this->assertEquals(count($questionReports), 2);
    }

    public function testSearch()
    {
        $this->getAnswerQuestionReportDao()->batchCreate([
            [
                'id' => '1',
                'identify' => '1_1',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ],
            [
                'id' => '2',
                'identify' => '1_2',
                'answer_record_id' => '1',
                'total_score' => 2,
                'section_id' => '1',
                'item_id' => '2',
                'question_id' => '2',
                'response' => ['A'],
            ]
        ]);

        $questionReports = $this->getAnswerQuestionReportService()->search([], [], 0, 2);

        $this->assertEquals(count($questionReports), 2);
    }

    protected function getAnswerQuestionReportService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    protected function getAnswerQuestionReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerQuestionReportDao');
    }
}
