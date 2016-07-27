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
            'course.material.create'    => 'onMaterialCreate',
            'course.material.delete'    => 'onMaterialDelete'
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

        if ($material && $material['source'] == 'opencoursematerial' && $material['type'] == 'openCourse') {
            $this->getOpenCourseService()->waveCourseLesson($material['lessonId'], 'materialNum', 1);
        }
    }

    public function onMaterialUpdate(ServiceEvent $event)
    {
        $context   = $event->getSubject();
        $argument  = $context['argument'];
        $material  = $context['material'];

        $lesson = $this->getOpenCourseService()->getCourseLesson($material['courseId'], $material['lessonId']);

        if ($lesson && $material['lessonId'] && $material['source'] == 'opencoursematerial') {
            $this->getOpenCourseService()->waveCourseLesson($material['lessonId'], 'materialNum', 1);
        }
    }

    public function onMaterialDelete(ServiceEvent $event)
    {
        $material = $event->getSubject();

        $lesson = $this->getOpenCourseService()->getCourseLesson($material['courseId'], $material['lessonId']);

        if ($lesson) {
            if ($material['lessonId'] && $material['source'] == 'opencourselesson' && $material['type'] == 'openCourse') {
                $this->getOpenCourseService()->resetLessonMediaId($material['lessonId']);
            }
            
            if ($material['lessonId'] && $material['source'] == 'opencoursematerial' && $material['type'] == 'openCourse'){
               $this->getOpenCourseService()->waveCourseLesson($material['lessonId'], 'materialNum', -1);
            }
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
