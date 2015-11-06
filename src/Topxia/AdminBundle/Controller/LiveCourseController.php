<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Service\Util\EdusohoLiveClient;

class LiveCourseController extends BaseController
{

    public function indexAction (Request $request, $status)
    {
        $conditions = $request->query->all();
        if(empty($conditions['titleLike'])){
            unset($conditions['titleLike']);
        }
            
        $courses = $this->getCourseService()->searchCourses(array('type' => 'live','status' => 'published'), $sort = 'latest', 0, 1000);

        $courseIds = ArrayToolkit::column($courses, 'id');

        $courses = ArrayToolkit::index($courses, 'id');

        $conditions['type']="live";

        if($status == 'coming'){
            $conditions['startTimeGreaterThan'] = isset($conditions['startDateTime'])?strtotime($conditions['startDateTime']):time();
            $conditions['startTimeLessThan'] = isset($conditions['endDateTime'])?strtotime($conditions['endDateTime']):null;
        }
        if($status == 'end'){
            $conditions['endTimeLessThan'] = isset($conditions['endDateTime'])?strtotime($conditions['endDateTime']):time();
            $conditions['startTimeGreaterThan'] = isset($conditions['startDateTime'])?strtotime($conditions['startDateTime']):null;
        }
        if($status == 'underway'){
            $conditions['startDateTime'] = isset($conditions['startDateTime'])?strtotime($conditions['startDateTime']):time();
            $conditions['endTimeLessThan'] = isset($conditions['endDateTime'])?strtotime($conditions['endDateTime']):time();
        }


        $conditions['courseIds'] = $courseIds;
        $conditions['status'] ='published';


        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchLessonCount($conditions),
            20
        );
        if ($status == 'end') {
            $lessons = $this->getCourseService()->searchLessons($conditions,
                array('startTime', 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        } else {
            $lessons = $this->getCourseService()->searchLessons($conditions, 
                array('startTime', 'ASC'), 
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }
        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaAdminBundle:LiveCourse:index.html.twig', array(
            'status' => $status,
            'lessons' => $lessons,
            'courses' => $courses,
            'paginator' => $paginator,
            'default'=> $default,
        ));
    }

    public function getMaxOnlineAction(Request $request)
    {
        $conditions = $request->query->all();
        if(!empty($conditions['courseId']) && !empty($conditions['lessonId'])){
            $lesson = $this->getCourseService()->getCourseLesson($conditions['courseId'], $conditions['lessonId']);

            $client = new EdusohoLiveClient();
            if ($lesson['type'] == 'live') {
                $result = $client->getMaxOnline($lesson['mediaId']);
                $lesson = $this->getCourseService()->setCourseLessonMaxOnlineNum($lesson['id'],$result['onLineNum']);
            } 
        }

        return $this->createJsonResponse($lesson);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}