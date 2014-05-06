<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class LiveCourseLessonManageController extends BaseController
{
	
  	public function createAction(Request $request,$id)
    {
        $liveCourse = $this->getCourseService()->tryManageCourse($id);

        if($request->getMethod() == 'POST') {

            $liveLesson = $request->request->all();
            $liveLesson['type'] = 'live';
            $liveLesson['courseId'] = $liveCourse['id'];
            $liveLesson = $this->getCourseService()->createLesson($liveLesson);
			return $this->render('TopxiaWebBundle:LiveCourseLessonManage:live-list-item.html.twig', array(
				'course' => $liveCourse,
				'lesson' => $liveLesson,
			));
        }
            
        return $this->render('TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig',array(
        	'liveCourse' => $liveCourse,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}