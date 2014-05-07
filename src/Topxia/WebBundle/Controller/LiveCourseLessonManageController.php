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

    public function LessonTimeCheckAction(Request $request,$id)
    {
        $data = $request->query->all();
        $startTime = $data['startTime'];
        $endTime = $data['endTime'];

        list($result, $message) = $this->getCourseService()->lessonTimeCheck($id,$startTime,$endTime);

        if ($result == 'success') {
            $response = array('success' => true, 'message' => '这个时间段的课时可以创建');
        } else {
            $response = array('success' => false, 'message' => $message);
        }
        return $this->createJsonResponse($response);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}