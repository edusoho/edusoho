<?php
namespace Classroom\Service\Classroom\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class CourseLessonEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'course.lesson.delete' => array('onCourseLessonDelete', 0),
            'course.lesson.create' => array('onCourseLessonCreate', 0),
            'course.teacher.update' => array('onCourseTeacherUpdate', 0),
        );
    }

    public function onCourseLessonCreate(ServiceEvent $event)
	{
		$context = $event->getSubject();

		$id = $context["courseId"];

		$classrooms = $this->getClassroomService()->findClassroomsByCourseId($id);
		$classroomIds = ArrayToolkit::column($classrooms,'classroomId');
		foreach ($classroomIds as $key => $value) {
			$classroom=$this->getClassroomService()->getClassroom($value);
			$lessonNum=$classroom['lessonNum']+1;
			$this->getClassroomService()->updateClassroom($value,array("lessonNum"=>$lessonNum));
		}
	}

	public function onCourseLessonDelete(ServiceEvent $event)
	{
		$context = $event->getSubject();

		$courseId = $context["courseId"];

		$classrooms = $this->getClassroomService()->findClassroomsByCourseId($courseId);
		$classroomIds = ArrayToolkit::column($classrooms,'classroomId');
		foreach ($classroomIds as $key => $value) {
			$classroom = $this->getClassroomService()->getClassroom($value);
			$lessonNum = $classroom['lessonNum']-1;
			$this->getClassroomService()->updateClassroom($value,array("lessonNum"=>$lessonNum));
		}
	}

    public function onCourseTeacherUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();

        $courseId = $context["courseId"];

        $findClassroomsByCourseId =  ArrayToolkit::column($this->getClassroomService()->findClassroomsByCourseId($courseId),'classroomId');

        foreach ($findClassroomsByCourseId as $key => $value) 
        {
            $this->getClassroomService()->updateClassroomTeachers($value);
        }
    }

    private function getClassroomService(){
		return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

}