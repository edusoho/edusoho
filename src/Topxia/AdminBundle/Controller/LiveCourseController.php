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

        if(!empty($conditions['courseId']) && !empty($conditions['lessonId'])){
            $course = $this->getCourseService()->tryManageCourse($conditions['courseId']);
            $lesson = $this->getCourseService()->getCourseLesson($conditions['courseId'], $conditions['lessonId']);
            $client = new EdusohoLiveClient();
            if ($lesson['type'] == 'live') {
                $result = $client->getMaxOnline($lesson['mediaId']);
                $lesson = $this->getCourseService()->setCourseLessonMaxOnlineNum($lesson['id'],$result['onLineNum']);
            } 
        }//如果有courseId，应该unset掉,否则影响搜索lesson
        unset($conditions['courseId']);
                
        switch ($status) {
            case 'coming':
                $conditions['startTimeGreaterThan'] = time();
                break;
            case 'end':
                $conditions['endTimeLessThan'] = time();
                break;
            case 'underway':
                $conditions['startTimeLessThan'] = time();
                $conditions['endTimeGreaterThan'] = time();
                break;
        }
        //有搜索时间条件的时候unset之前给的搜索时间信息
        if(!empty($conditions['startDateTime']) && !empty($conditions['endDateTime'])){
            if($status == 'end'){ unset($conditions['endTimeLessThan']);}
            if($status == 'underway'){ unset($conditions['endTimeGreaterThan']);}
            $conditions['startTimeGreaterThan'] = strtotime($conditions['startDateTime']);
            $conditions['startTimeLessThan'] = strtotime($conditions['endDateTime']);
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

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}