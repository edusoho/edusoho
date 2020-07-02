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
                'name' => 'questionBank'
            ]
        );

        $result = $subscriber->onQuestionBankUpdate($event);
        $this->assertNull($result);
    }
}