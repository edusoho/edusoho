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
            $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
            $liveLesson['length'] = $liveLesson['length'];

            $liveLesson = $this->getCourseService()->createLesson($liveLesson);
			return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
				'course' => $liveCourse,
				'lesson' => $liveLesson,
			));
        }
            
        return $this->render('TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig',array(
        	'liveCourse' => $liveCourse,
        ));
    }

    public function editAction(Request $request,$courseId,$lessonId)
    {
        $liveCourse = $this->getCourseService()->tryManageCourse($courseId);
        $liveLesson = $this->getCourseService()->getCourseLesson($liveCourse['id'], $lessonId);

        if($request->getMethod() == 'POST') {

            $liveLesson = $request->request->all();
            $liveLesson['type'] = 'live';
            $liveLesson['courseId'] = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
            $liveLesson['free'] = empty($liveLesson['free']) ? 0 : $liveLesson['free'];

            $liveLesson = $this->getCourseService()->updateLesson($courseId,$lessonId,$liveLesson);
            
            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $liveCourse,
                'lesson' => $liveLesson,
            ));
        }
            
        return $this->render('TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig',array(
            'liveCourse' => $liveCourse,
            'liveLesson' => $liveLesson
        ));
    }

    public function lessonTimeCheckAction(Request $request,$id)
    {
        $data = $request->query->all();

        $startTime = $data['startTime'];
        $length = $data['length'];
        $lessonId = empty($data['lessonId']) ? "" : $data['lessonId'];

        list($result, $message) = $this->getCourseService()->liveLessonTimeCheck($id,$lessonId,$startTime,$length);

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