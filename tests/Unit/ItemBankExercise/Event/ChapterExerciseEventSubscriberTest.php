<?php

namespace Tests\Unit\ItemBankExercise\Event;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Event\ChapterExerciseEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class ChapterExerciseEventSubscriberTest extends BaseTestCase
{
    public function testOnAnswerSubmitted()
    {
        $this->mockChapterExerciseRecord();
        $this->mockAnswerReportService();

        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'status' => 'reviewing',
                'answer_report_id' => 1,
            ]
        );

        $subscriber->onAnswerSubmitted($event);
        $record = $this->getItemBankChapterExerciseRecordDao()->get(1);
        $this->assertEquals($record['status'], 'reviewing');
        $this->assertEquals($record['doneQuestionNum'], 1);
    }

    public function testOnAnswerSaved()
    {
        $this->mockChapterExerciseRecord();

        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'answer_record_id' => 1,
                'section_responses' => [
                    [
                        'item_responses' => [
                            [
                                'question_responses' => [
                                    ['response' => ['A']],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $subscriber->onAnswerSaved($event);
        $record = $this->getItemBankChapterExerciseRecordDao()->get(1);
        $this->assertEquals($record['status'], 'doing');
        $this->assertEquals($record['doneQuestionNum'], 1);
    }

    public function testOnAnswerFinished()
    {
        $this->mockChapterExerciseRecord();
        $this->mockAnswerReportService();
        $this->mockMember();
        $this->mockItemBankExerciseService();
        $this->mockQuestionBankService();

        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event(['id' => 1, 'answer_record_id' => 1]);

        $subscriber->onAnswerFinished($event);
        $record = $this->getItemBankChapterExerciseRecordDao()->get(1);
        $member = $this->getExerciseMemberDao()->get(1);
        $this->assertEquals($record['doneQuestionNum'], 1);
        $this->assertEquals($record['rightQuestionNum'], 1);
        $this->assertEquals($record['status'], 'finished');
        $this->assertEquals($record['rightRate'], 50.0);
        $this->assertEquals($member['doneQuestionNum'], 1);
        $this->assertEquals($member['rightQuestionNum'], 1);
        $this->assertEquals($member['masteryRate'], 50.0);
        $this->assertEquals($member['completionRate'], 50.0);
    }

    public function testOnItemCreate()
    {
        $this->mockCreateUpdateMemberMasteryRateJob();
        $this->mockQuestionBankService();
        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event(['bank_id' => 1]);
        $subscriber->onItemCreate($event);
    }

    public function testOnItemUpdate()
    {
        $this->mockItemBankExerciseQuestionRecordService();
        $this->mockCreateUpdateMemberMasteryRateJob();
        $this->mockQuestionBankService();
        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event(['id' => 1, 'bank_id' => 1], ['originItem' => ['id' => 1]]);
        $subscriber->onItemUpdate($event);
    }

    public function testOnItemDelete()
    {
        $this->mockItemBankExerciseQuestionRecordService();
        $this->mockCreateUpdateMemberMasteryRateJob();
        $this->mockQuestionBankService();
        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event(['id' => 1, 'bank_id' => 1]);
        $subscriber->onItemDelete($event);
    }

    public function testOnItemImport()
    {
        $this->mockCreateUpdateMemberMasteryRateJob();
        $this->mockQuestionBankService();
        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event([['bank_id' => 1]]);
        $subscriber->onItemImport($event);
    }

    public function testOnItemBatchDelete()
    {
        $this->mockItemBankExerciseQuestionRecordService();
        $this->mockCreateUpdateMemberMasteryRateJob();
        $this->mockQuestionBankService();
        $subscriber = new ChapterExerciseEventSubscriber($this->biz);
        $event = new Event([['id' => 1, 'bank_id' => 1]]);
        $subscriber->onItemBatchDelete($event);
    }

    protected function mockChapterExerciseRecord()
    {
        $this->getItemBankChapterExerciseRecordDao()->create([
            'id' => 1,
            'answerRecordId' => 1,
            'userId' => 1,
            'moduleId' => 1,
            'exerciseId' => 1,
        ]);
    }

    protected function mockMember()
    {
        $this->getExerciseMemberDao()->create([
            'id' => 1,
            'userId' => 1,
            'exerciseId' => 1,
        ]);
    }

    protected function mockItemBankExerciseService()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'questionBankId' => 1,
                    ],
                ],
            ]
        );
    }

    protected function mockQuestionBankService()
    {
        $this->mockBiz(
            'QuestionBank:QuestionBankService',
            [
                [
                    'functionName' => 'getQuestionBank',
                    'returnValue' => [
                        'itemBank' => [
                            'question_num' => 2,
                        ],
                    ],
                ],
                [
                    'functionName' => 'getQuestionBankByItemBankId',
                    'returnValue' => [
                        'id' => 1,
                    ],
                ],
            ]
        );
    }

    protected function mockCreateUpdateMemberMasteryRateJob()
    {
        $this->mockBiz(
            'Scheduler:SchedulerService',
            [
                [
                    'functionName' => 'register',
                    'returnValue' => [],
                ],
            ]
        );
    }

    protected function mockAnswerReportService()
    {
        $this->mockBiz(
            'ItemBank:Answer:AnswerReportService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [
                        'right_question_count' => 1,
                        'right_rate' => 50.0,
                        'section_reports' => [
                            [
                                'item_reports' => [
                                    [
                                        'item_id' => 1,
                                        'question_reports' => [
                                            [
                                                'response' => ['A'],
                                                'status' => 'right',
                                                'question_id' => 1,
                                            ],
                                            [
                                                'response' => [],
                                                'status' => 'no_answer',
                                                'question_id' => 2,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    protected function mockItemBankExerciseQuestionRecordService()
    {
        $this->mockBiz(
            'ItemBankExercise:ExerciseQuestionRecordService',
            [
                [
                    'functionName' => 'deleteByItemIds',
                    'returnValue' => [],
                ],
                [
                    'functionName' => 'deleteByQuestionIds',
                    'returnValue' => [],
                ],
            ]
        );
    }

    protected function getItemBankChapterExerciseRecordDao()
    {
        return $this->createDao('ItemBankExercise:ChapterExerciseRecordDao');
    }

    protected function getExerciseMemberDao()
    {
        return $this->createDao('ItemBankExercise:ExerciseMemberDao');
    }
}
