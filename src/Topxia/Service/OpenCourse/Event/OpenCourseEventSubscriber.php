<?php
namespace Topxia\Service\OpenCourse\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OpenCourseEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'open.course.lesson.create' => 'onLessonCreate',
            'open.course.lesson.delete' => 'onLessonDelete',
            'open.course.member.create' => 'onMemberCreate'
        );
    }

    public function onLessonCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        $lessonNum = $this->getOpenCourseService()->searchLessonCount(array('courseId' => $lesson['courseId']));
        $this->getOpenCourseService()->updateCourse($lesson['courseId'], array('lessonNum' => $lessonNum));
    }

    public function onLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        $lessonNum = $this->getOpenCourseService()->searchLessonCount(array('courseId' => $lesson['courseId']));
        $this->getOpenCourseService()->updateCourse($lesson['courseId'], array('lessonNum' => $lessonNum));
    }

    public function onMemberCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $fields  = $context['argument'];
        $member  = $context['newMember'];

        $memberNum = $this->getOpenCourseService()->searchMemberCount(array('courseId' => $fields['courseId']));

        $this->getOpenCourseService()->updateCourse($fields['courseId'], array('studentNum' => $memberNum));
    }

    protected function getNoteService()
    {
        return ServiceKernel::instance()->createService('Course.NoteService');
    }

    protected function getOpenCourseService()
    {
        return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
    }
}
