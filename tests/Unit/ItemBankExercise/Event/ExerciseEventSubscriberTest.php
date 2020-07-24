<?php

namespace Tests\Unit\ItemBankExercise\Event;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Event\ExerciseEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class ExerciseEventSubscriberTest extends BaseTestCase
{
    public function testOnQuestionBankUpdate()
    {
        $subscriber = new ExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'categoryId' => 1,
                'name' => 'questionBank',
            ],
            [
                'members' => '1,2',
            ]
        );

        $result = $subscriber->onQuestionBankUpdate($event);
        $this->assertTrue($result);
    }

    public function testOnReviewChanged()
    {
        $subscriber = new ExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'targetType' => 'item_bank_exercise',
                'targetId' => 1,
            ]
        );

        $result = $subscriber->onReviewChanged($event);
        $this->assertNull($result);
    }

    public function testOnReviewChanged_whenTargetTypeInvalid_thenReturnFalse()
    {
        $subscriber = new ExerciseEventSubscriber($this->biz);
        $event = new Event(
            [
                'id' => 1,
                'targetType' => '222',
                'targetId' => 1,
            ]
        );

        $result = $subscriber->onReviewChanged($event);
        $this->assertEquals($result, false);
    }
}
