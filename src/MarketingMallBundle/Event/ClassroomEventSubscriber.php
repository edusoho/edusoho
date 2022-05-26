<?php

namespace MarketingMallBundle\Event;

use Codeages\Biz\Framework\Event\Event;
use MarketingMallBundle\Common\GoodsContentBuilder\ClassroomInfoBuilder;

class ClassroomEventSubscriber extends BaseEventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'classroom.course.create' => 'onClassroomCourseCreate',
            'classroom.course.delete' => 'onClassroomCourseDelete',
            'classroom.course.update' => 'onClassroomCourseUpdate',
        ];
    }

    public function onClassroomCourseCreate(Event $event)
    {
        $classroom = $event->getSubject();
        $newCourseIds = $event->getArgument('newCourseIds');
        if ($newCourseIds) {
            $this->syncClassroomToMarketingMall($classroom['id']);
        }
    }

    public function onClassroomCourseDelete(Event $event)
    {
        $classroom = $event->getSubject();
        $this->syncClassroomToMarketingMall($classroom['id']);
    }

    public function onClassroomCourseUpdate(Event $event)
    {
        $classroom = $event->getSubject();
        $courseIds = $event->getArgument('courseIds');
        $existCourseIds = $event->getArgument('existCourseIds');
        if ($courseIds != $existCourseIds) {
            $this->syncClassroomToMarketingMall($classroom['id']);
        }
    }

    protected function syncClassroomToMarketingMall($classroomId)
    {
        $this->updateGoodsContent('classroom', new ClassroomInfoBuilder(), $classroomId);
    }
}
