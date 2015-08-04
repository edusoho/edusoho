<?php
namespace Topxia\Service\Course\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;

class CourseLessonEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson.create' => array('onCourseLessonCreate', 0),
            'course.lesson.delete' => array('onCourseLessonDelete', 0),
            'course.lesson.update'=> 'onCourseLessonUpdate',
            'course.lesson_start' => 'onLessonStart',
            'course.lesson_finish' =>'onLessonFinish'
        );
    }

    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];
        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
        foreach ($classroomIds as  $classroomId) {
            $classroom = $this->getClassroomService()->getClassroom($classroomId);
            $lessonNum = $classroom['lessonNum']+1;
            $this->getClassroomService()->updateClassroom($classroomId, array("lessonNum" => $lessonNum));
        }

        $courseIds = $this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1);
        foreach ($courseIds as $courseId) {
            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
            foreach ($classroomIds as  $classroomId) {
                $classroom = $this->getClassroomService()->getClassroom($classroomId);
                $lessonNum = $classroom['lessonNum']+1;
                $this->getClassroomService()->updateClassroom($classroomId, array("lessonNum" => $lessonNum));
            }
        }
        
        $lesson = $context["lesson"];
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $this->getCourseService()->updateCourseByParentIdAndLocked($lesson['courseId'], 1, array("lessonNum"=>$course['lessonNum']));
        if (!empty($courseIds)){
            $lesson ['parentId'] = $lesson ['id'];
            unset($lesson ['id'],$lesson['courseId']);
            foreach ($courseIds as $courseId)
            {   
                $lesson['courseId'] = $courseId;
                $this->getCourseService()->addLesson($lesson);
            }
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
        foreach ($classroomIds as $key => $value) {
            $classroom = $this->getClassroomService()->getClassroom($value);
            $lessonNum = $classroom['lessonNum']-1;
            $this->getClassroomService()->updateClassroom($value, array("lessonNum" => $lessonNum));
        }

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        foreach ($courseIds as $courseId) {
            $classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
            foreach ($classroomIds as  $classroomId) {
                $classroom = $this->getClassroomService()->getClassroom($classroomId);
                $lessonNum = $classroom['lessonNum']-1;
                $this->getClassroomService()->updateClassroom($classroomId, array("lessonNum" => $lessonNum));
            }
        }

        $lesson = $context["lesson"];
        $this->getCourseService()->deleteLessonByParentId($lesson['id']);
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $this->getCourseService()->updateCourseByParentIdAndLocked($lesson['courseId'], 1, array("lessonNum"=>$course['lessonNum']));
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $parentId = $lesson['id'];
        unset($lesson['id'],$lesson['courseId'],$lesson['chapterId'],$lesson['parentId']);
        $this->getCourseService()->updateLessonByParentId($parentId,$lesson);
    }

    public function onLessonStart(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'start_learn_lesson',
            'courseId' => $course['id'],
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            ),
        ));
    }

    public function onLessonFinish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $this->getStatusService()->publishStatus(array(
            'type' => 'learned_lesson',
            'courseId' => $course['id'],
            'objectType' => 'lesson',
            'objectId' => $lesson['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'course' => $this->simplifyCousrse($course),
                'lesson' => $this->simplifyLesson($lesson),
            ),
        ));
    }


    protected function simplifyCousrse($course)
    {
        return array(
            'id' => $course['id'],
            'title' => $course['title'],
            'picture' => $course['middlePicture'],
            'type' => $course['type'],
            'rating' => $course['rating'],
            'about' => StringToolkit::plain($course['about'], 100),
            'price' => $course['price'],
        );
    }

    protected function simplifyLesson($lesson)
    {
        return array(
            'id' => $lesson['id'],
            'number' => $lesson['number'],
            'type' => $lesson['type'],
            'title' => $lesson['title'],
            'summary' => StringToolkit::plain($lesson['summary'], 100),
        );
    }

    protected function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }
}
