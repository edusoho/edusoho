<?php
 
namespace Topxia\WebBundle\Handler;
 
use Topxia\Service\Common\ServiceEvent;
use Symfony\Component\Security\Core\SecurityContext;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
 
class CourseLessonHandler
{
	private $securityContext;
	
	public function __construct(SecurityContext $securityContext)
	{
		$this->securityContext = $securityContext;
	}
	
	public function onCourseLessonCreate(ServiceEvent $event)
	{
		$context = $event->getSubject();

		$id = $context["courseId"];

		$app = $this->getAppService()->findInstallApp('Classroom');
		if ($app) {
			$classrooms = $this->getClassroomService()->findClassroomsByCourseId($id);
			$classroomIds = ArrayToolkit::column($classrooms,'classroomId');
			foreach ($classroomIds as $key => $value) {
				$classroom=$this->getClassroomService()->getClassroom($value);
				$lessonNum=$classroom['lessonNum']+1;
				$this->getClassroomService()->updateClassroom($value,array("lessonNum"=>$lessonNum));
			}
		}
	}

	public function onCourseLessonDelete(ServiceEvent $event)
	{
		$context = $event->getSubject();

		$courseId = $context["courseId"];

		$app = $this->getAppService()->findInstallApp('Classroom');
		if ($app) {
			$classrooms = $this->getClassroomService()->findClassroomsByCourseId($courseId);
			$classroomIds = ArrayToolkit::column($classrooms,'classroomId');
			foreach ($classroomIds as $key => $value) {
				$classroom = $this->getClassroomService()->getClassroom($value);
				$lessonNum = $classroom['lessonNum']-1;
				$this->getClassroomService()->updateClassroom($value,array("lessonNum"=>$lessonNum));
			}
		}
	}

	private function getAppService(){
		return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }

    private function getClassroomService(){
		return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }
	
}