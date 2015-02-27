<?php
 
namespace Topxia\WebBundle\Handler;
 
use Topxia\Event\InteractiveEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
 
/**
 * Custom login listener.
 */
class CourseLessonHandler
{
	/** @var \Symfony\Component\Security\Core\SecurityContext */
	private $securityContext;
	
	public function __construct(SecurityContext $securityContext)
	{
		$this->securityContext = $securityContext;
	}
	
	public function onCourseLessonCreate(InteractiveEvent $event)
	{
		$context = $event->getContext();

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

	public function onCourseLessonDelete(InteractiveEvent $event)
	{
		$context = $event->getContext();

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