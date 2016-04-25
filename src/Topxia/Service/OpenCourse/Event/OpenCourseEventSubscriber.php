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
            'open.course.member.create' => 'onMemberCreate',
            'material.create'           => 'onMaterialCreate',
            'material.delete'           => 'onMaterialDelete'
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

    public function onMaterialCreate(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $material = $context['material'];

        if ($material) {
            $this->getOpenCourseService()->waveCourseLesson($material['lessonId'], 'materialNum', 1);
        }
    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $context  = $event->getSubject();
        $material = $context['material'];

        if (!empty($material['fileId'])) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', -1);
        }
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
