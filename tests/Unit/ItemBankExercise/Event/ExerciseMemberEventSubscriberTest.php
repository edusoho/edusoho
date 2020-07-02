<?php


namespace Tests\Unit\ItemBankExercise\Event;


use Biz\BaseTestCase;
use Biz\ItemBankExercise\Event\ExerciseMemberEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class ExerciseMemberEventSubscriberTest extends BaseTestCase
{
    public function testOnExerciseJoin()
    {
        $subscriber = new ExerciseMemberEventSubscriber($this->biz);
        $event = new Event(
            ['id' => 1],
            ['member' => ['role' => 'student']]
        );
        $result = $subscriber->onExerciseJoin($event);
        $this->assertNull($result);
    }
}