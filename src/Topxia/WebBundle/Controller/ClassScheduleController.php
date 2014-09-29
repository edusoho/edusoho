<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassScheduleController extends ClassBaseController
{
    public function showAction(Request $request, $classId)
    {
        $class = $this->tryViewClass($classId);
        return $this->render("TopxiaWebBundle:ClassSchedule:show.html.twig", array(
            "class" => $class,
        ));
    }

    public function coursesAction(Request $request, $class)
    {
    	$user = $this->getCurrentUser();
    	$courses = array();
    	if($user->isAdmin()) {
    		$courses = $this->getCourseService()->findCoursesByClassId($class['id']);
    	}
    	
    	return $this->render('TopxiaWebBundle:ClassSchedule:courses.html.twig', array(
    		'courses' => $courses,
    		));
    }

    public function getItemsAction($courseId)
    {
        $items = $this->getCourseService()->getCourseItems($courseId);
        $course = $this->getCourseService()->getCourse($courseId);
        return $this->render('TopxiaWebBundle:ClassSchedule:item-list.html.twig', array(
            'items' => $items,
            'course' => $course,
            ));
    }

    public function scheduleAction(Request $request, $classId)
    {
        $previewAs = $request->query->get('previewAs') ? : 'week';
        $sunDay = $request->query->get('sunDay');
        $yearMonth = $request->query->get('yearMonth');
        if($previewAs == 'week') {
            $results = $this->getScheduleService()->findScheduleLessonsByWeek($classId, $sunDay);
        } else {
            $results = $this->getScheduleService()->findScheduleLessonsByMonth($classId, $yearMonth);            
        }
        return $this->render("TopxiaWebBundle:ClassSchedule:tr-{$previewAs}.html.twig", array(
            'results' => $results,
            ));
    }

    private function getScheduleService()
    {
        return $this->getServiceKernel()->createService('Schedule.ScheduleService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}