<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class LiveCourseController extends BaseController
{
	public function exploreAction(Request $request)
	{
        if (!$this->setting('course.live_course_enabled')) {
            return $this->createMessageResponse('info', '直播频道已关闭');
        }

        $group = $this->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->findGroupRootCategories($group['code']);
        }

        $conditions = array(
            'status' => 'published',
            'isLive' => '1'
        );

        $courses = $this->getCourseService()->searchCourses( $conditions, 'lastest',0,1000 );

        $courseIds = ArrayToolkit::column($courses, 'id');

        $lessonCondition['courseIds'] = $courseIds;
        $lessonCondition['status'] = 'published';

        $lessons = $this->getCourseService()->searchLessons(
            $lessonCondition,  
            array('startTime', 'ASC'), 0, 10
        );

        $newCourses = array();

        $courses = ArrayToolkit::index($courses, 'id');

        foreach ($lessons as $key => &$lesson) {
            $newCourses[$key] = $courses[$lesson['courseId']];
            $newCourses[$key]['lesson'] = $lesson;
        }

        $now = time();

        $today = date("Y-m-d");

        $tomorrow = strtotime("$today tomorrow");

        $theDayAfterTomorrow = strtotime("$today +2 day");

        $today = strtotime("$today");

        $recenntLessonsCondition = array(
            'status' => 'published',
            'startTimeGreaterThan' => $now,
            'endTimeLessThan' => $theDayAfterTomorrow
        );

        $recentlessons = $this->getCourseService()->searchLessons(
            $recenntLessonsCondition,  
            array('startTime', 'ASC'), 0, 100
        );

        $recentCourses = array();
        $userIds = array();

        foreach ($recentlessons as $key => &$lesson) {
            $recentCourses[$key] = $courses[$lesson['courseId']];
            $recentCourses[$key]['lesson'] = $lesson;
        }

        foreach ($recentCourses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);
        $conditions['ratingGreaterThan'] = 0.01;

        $ratingCourses = $this->getCourseService()->searchCourses( $conditions, 'Rating',0,10);
        
        return $this->render('TopxiaWebBundle:LiveCourse:index.html.twig',array(
            'categories' => $categories,
            'newCourses' => $newCourses,
            'recentlessons' => $recentlessons,
            'recentCourses' => $recentCourses,
            'users' => $users,
            'tomorrow' => $tomorrow,
            'ratingCourses' => $ratingCourses
        ));

	}

    public function listAction(Request $request, $category)
    {   
        if (!$this->setting('course.live_course_enabled')) {
            return $this->createMessageResponse('info', '直播频道已关闭');
        }
        
        $now = time();

        $today = date("Y-m-d");

        $tomorrow = strtotime("$today tomorrow");

        $theDayAfterTomorrow = strtotime("$today +2 day");

        $nextweek = strtotime("$today next week");

        $lastweek = strtotime("$today last week");

        $today = strtotime("$today");

        if (!empty($category)) {
            if (ctype_digit((string) $category)) {
                $category = $this->getCategoryService()->getCategory($category);
            } else {
                $category = $this->getCategoryService()->getCategoryByCode($category);
            }

            if (empty($category)) {
                throw $this->createNotFoundException();
            }
        } else {
            $category = array('id' => null);
        }

        $group = $this->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->getCategoryTree($group['id']);
        }

        $date = $request->query->get('date', 'today');

        $conditions = array(
            'status' => 'published',
            'categoryId' => $category['id'],
            'isLive' => '1'
        );

        $courses = $this->getCourseService()->searchCourses( $conditions, 'lastest',0,1000 );

        $courseIds = ArrayToolkit::column($courses, 'id');

        switch ($date) {
            case 'today':
                $lessonCondition['startTimeGreaterThan'] = $today;
                $lessonCondition['endTimeLessThan'] = $tomorrow;
                break;
            case 'tomorrow':
                $lessonCondition['startTimeGreaterThan'] = $tomorrow;
                $lessonCondition['endTimeLessThan'] = $theDayAfterTomorrow;
                break;
            case 'nextweek':
                $lessonCondition['startTimeGreaterThan'] = $tomorrow;
                $lessonCondition['endTimeLessThan'] = $nextweek;
                break;
            case 'lastweek':
                $lessonCondition['startTimeGreaterThan'] = $lastweek;
                $lessonCondition['endTimeLessThan'] = $today;
                break;
        }

        $lessonCondition['courseIds'] = $courseIds;
        $lessonCondition['status'] = 'published';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchLessonCount($lessonCondition)
            , 10
        );

        $lessons = $this->getCourseService()->searchLessons(
            $lessonCondition,  
            array('startTime', 'ASC'), 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ($date == 'today') {
            $lessons = $this->getCourseService()->searchLessons( $lessonCondition,  array('startTime', 'ASC'), 0, 1000 );
            $finishedLessons = array();
            foreach ($lessons as &$lesson) {
                if ($now > $lesson['endTime'] ) {
                    $finishedLessons[] = $lesson;
                    $lesson = '';
                }
            }
            $lessons = array_merge($lessons, $finishedLessons);
            $lessons = array_filter($lessons);
        }

        $newCourses = array();

        if ($courses) {
            $courses = ArrayToolkit::index($courses, 'id');

            foreach ($lessons as $key => &$lesson) {
                $newCourses[$key] = $courses[$lesson['courseId']];
                $newCourses[$key]['lesson'] = $lesson;
            }
        }

        $popularCourses = $this->getCourseService()->searchCourses( array( 'status' => 'published', 'isLive' => '1' ), 'hitNum',0,10 );

        return $this->render('TopxiaWebBundle:LiveCourse:list.html.twig',array(
            'date' => $date,
            'category' => $category,
            'categories' => $categories,
            'paginator' => $paginator,
            'courses' => $courses,
            'popularCourses' => $popularCourses,
            'lessons' => $lessons,
            'newCourses' => $newCourses
        ));
    }

  	public function createAction(Request $request)
    {
        if($request->getMethod() == 'POST') {
            $data = $request->query->all();
            var_dump($data);
            exit();
        }
            
        return $this->render('TopxiaWebBundle:LiveCourse:live-lesson-modal.html.twig',array(
        	
        ));
    }

    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {   

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:LiveCourse:live-courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users' => $users,
            'mode' => $mode,
        ));
    }

    public function playAction(Request $request,$courseId,$lessonId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        if (!$this->getCourseService()->canTakeCourse($courseId)) {
            return $this->createMessageResponse('info', '您还不是该课程的学员，请先购买加入学习。', null, 3000, $this->generateUrl('course_show', array('id' => $courseId)));
        } 

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        if ($lesson['startTime'] <= (time()+60*30)) {

            $url = 'http://www.tetequ.com/api2/get_room_num';
            $params = array(
            'room_title' =>$lesson['title'],
            'description' => $lesson['summary']."#".time(),
            'begin_time' => $lesson['startTime'],
            'take_time' => 21599,
            'is_show' => 0,
            'company_code' => 'DZHKJ'
            );
            
            $result = $this->postRequest($url, $params);
            $result = json_decode($result, true);

            if (empty($result['room_num'])) {
                echo 'get room num error!';
                exit();
            }

            unset($result['success']);
            $liveLesson = $this->getCourseService()->createLiveRoomNum($courseId, $lessonId, $result);
        
        } else {
            return $this->createMessageResponse('info', '还没到登陆时间');
        }
    }

    public function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_URL, $url );

        // curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}