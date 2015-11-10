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
        $file = $request->query->all();
        $coursesTitle = null;

        if(!empty($file['keywordType']) and !empty($file['keyword'])){
            if($file['keywordType'] == 'courseTitle') {
                $coursesTitle = $file['keyword'];
            }
            if($file['keywordType'] == 'lessonTitle') {
                $conditions['titleLike'] = $file['keyword'];
            }
        }
            
        $courses = $this->getCourseService()->searchCourses(array('type' => 'live','status' => 'published','titleLike'=>$coursesTitle,), $sort = 'latest', 0, 1000);

        $courseIds = ArrayToolkit::column($courses, 'id');

        $courses = ArrayToolkit::index($courses, 'id');

        $conditions['type']="live";

        if($status == 'coming'){
            $conditions['startTimeGreaterThan'] = !empty($file['startDateTime'])?strtotime($file['startDateTime']):time();
            $conditions['startTimeLessThan'] = !empty($file['endDateTime'])?strtotime($file['endDateTime']):null;
        }
        if($status == 'end'){
            $conditions['endTimeLessThan'] = time();
            $conditions['startTimeLessThan'] = !empty($file['endDateTime'])?strtotime($file['endDateTime']):null;
            $conditions['startTimeGreaterThan'] = !empty($file['startDateTime'])?strtotime($file['startDateTime']):null;
        }
        if($status == 'underway'){
            $conditions['endTimeGreaterThan'] = time();
            $conditions['startTimeLessThan'] = !empty($file['endDateTime'])?strtotime($file['endDateTime']):time();
            $conditions['startTimeGreaterThan'] = !empty($file['startDateTime'])?strtotime($file['startDateTime']):null;
        }

        if(empty($courseIds)){//课时名出错时,这里不设置会默认搜出所有的courses
            $conditions['courseIds'] = array(
            '0'
            );
        }else{
        $conditions['courseIds'] = $courseIds;
        }

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