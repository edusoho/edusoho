<?php

namespace Tests\Unit\ItemBankExercise\Event;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Event\AssessmentExerciseEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class AssessmentExerciseEventSubscriberTest extends BaseTestCase
{
    public function testOnAnswerStarted()
    {
        $subscriber = new AssessmentExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'status' => 'reviewing',
            ]
        );

        $this->getItemBankAssessmentExerciseRecordDao()->create([
            'id' => 1,
            'answerRecordId' => 1,
        ]);

        $userFootprint = $subscriber->onAnswerStarted($event);
        $this->assertEquals($userFootprint['targetType'], 'item_bank_assessment_exercise');
        $this->assertEquals($userFootprint['targetId'], 1);
        $this->assertEquals($userFootprint['event'], 'answer.started');
    }

    public function testOnAnswerSubmitted()
    {
        $subscriber = new AssessmentExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'status' => 'reviewing',
            ]
        );

        $this->getItemBankAssessmentExerciseRecordDao()->create([
            'id' => 1,
            'answerRecordId' => 1,
        ]);

        $subscriber->onAnswerSubmitted($event);
        $record = $this->getItemBankAssessmentExerciseRecordDao()->get(1);
        $this->assertEquals($record['status'], 'reviewing');
    }

    public function testOnAnswerFinished()
    {
        $subscriber = new AssessmentExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'answer_record_id' => 1,
            ]
        );

        $this->getItemBankAssessmentExerciseRecordDao()->create([
            'id' => 1,
            'answerRecordId' => 1,
        ]);

        $subscriber->onAnswerFinished($event);
        $record = $this->getItemBankAssessmentExerciseRecordDao()->get(1);
        $this->assertEquals($record['status'], 'finished');
    }

    protected function getItemBankAssessmentExerciseRecordDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseRecordDao');
    }
}
