<?php
namespace Topxia\Service\OpenCourse\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\OpenCourse\Impl\OpenCourseServiceImpl;

class OpenCourseEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'open.course.lesson.create' => 'onLessonCreate',
            'open.course.lesson.delete' => 'onLessonDelete',
            'open.course.member.create' => 'onMemberCreate',
            'course.material.create'    => 'onMaterialCreate',
            'course.material.update'    => 'onMaterialUpdate',
            'course.material.delete'    => 'onMaterialDelete'
        );
    }

    public function onLessonCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];

        $course = $this->getOpenCourseService()->getCourse($lesson['courseId'], true);

        if (empty($course)) {
            throw new \RuntimeException('添加课时失败，课程不存在。');
        }

        if($course['status'] === 'draft' || $lesson['type'] === 'liveOpen'){
            $this->getOpenCourseService()->publishLesson($course['id'], $lesson['id']);
        }

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
        $context  = $event->getSubject();
        $argument = $context['argument'];
        $material = $context['material'];

        $lesson = $this->getOpenCourseService()->getCourseLesson($material['courseId'], $material['lessonId']);

        if ($material['source'] == 'opencoursematerial') {
            if ($material['lessonId']) {
                $this->getOpenCourseService()->waveCourseLesson($material['lessonId'], 'materialNum', 1);
            } elseif ($material['lessonId'] == 0 && isset($argument['lessonId']) && $argument['lessonId']) {
                $material['lessonId'] = $argument['lessonId'];
                $this->_waveLessonMaterialNum($material);
            }
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

            if ($material['lessonId'] && $material['source'] == 'opencoursematerial' && $material['type'] == 'openCourse') {
                $this->getOpenCourseService()->waveCourseLesson($material['lessonId'], 'materialNum', -1);
            }
        }
    }

    private function _waveLessonMaterialNum($material)
    {
        if ($material['lessonId'] && $material['source'] == 'opencoursematerial' && $material['type'] == 'openCourse') {
            $count = $this->getMaterialService()->searchMaterialCount(array(
                'courseId' => $material['courseId'],
                'lessonId' => $material['lessonId'],
                'source'   => 'opencoursematerial',
                'type'     => 'openCourse'
            )
            );
            $this->getOpenCourseService()->updateLesson($material['courseId'], $material['lessonId'], array('materialNum' => $count));
            return true;
        }

        return false;
    }

    protected function getNoteService()
    {
        return ServiceKernel::instance()->createService('Course.NoteService');
    }

    /**
     * @return OpenCourseServiceImpl
     */
    protected function getOpenCourseService()
    {
        return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
    }

    protected function getMaterialService()
    {
        return ServiceKernel::instance()->createService('Course.MaterialService');
    }
}
