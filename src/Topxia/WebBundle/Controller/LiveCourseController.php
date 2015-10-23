<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;

class LiveCourseController extends BaseController
{
    public function liveCapacityAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $client = new EdusohoLiveClient();
        $liveCapacity = $client->getCapacity();

        return $this->createJsonResponse($liveCapacity);

    }

	public function exploreAction(Request $request)
	{
        if (!$this->setting('course.live_course_enabled')) {
            return $this->createMessageResponse('info', '直播频道已关闭');
        }

        $recenntLessonsCondition = array(
            'status' => 'published',
            'endTimeGreaterThan' => time()
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchLessonCount($recenntLessonsCondition)
            , 30
        );

        $recentlessons = $this->getCourseService()->searchLessons(
            $recenntLessonsCondition,  
            array('startTime', 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($recentlessons, 'courseId'));

        $recentCourses = array();
        foreach ($recentlessons as $lesson) {
            $course = $courses[$lesson['courseId']];
            if ($course['status'] != 'published' || $course['parentId'] != '0') {
                continue;
            }
            $course['lesson'] = $lesson;
            $recentCourses[] = $course;

        }

        $liveCourses = $this->getCourseService()->searchCourses( array(
            'status' => 'published',
            'type' => 'live',
            'parentId' => '0'
        ), 'lastest',0, 10 );

        $userIds = array();
        foreach ($liveCourses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);
        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaWebBundle:LiveCourse:index.html.twig',array(
            'recentCourses' => $recentCourses,
            'liveCourses' => $liveCourses,
            'users' => $users,
            'paginator' => $paginator,
            'default'=> $default
        ));
	}

    public function ratingCoursesBlockAction()
    {   
        $conditions = array(
            'status' => 'published',
            'type' => 'live',
            'parentId' => '0',
            'ratingGreaterThan' => 0.01
        );

        $courses = $this->getCourseService()->searchCourses( $conditions, 'Rating',0,10);

        return $this->render('TopxiaWebBundle:LiveCourse:rating-courses-block.html.twig', array(
            'courses' => $courses
        ));
    }

    public function coursesBlockAction($courses, $view = 'list', $mode = 'default')
    {   

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, empty($course['teacherIds']) ? array() : $course['teacherIds']) ;
        }
        $users = $this->getUserService()->findUsersByIds($userIds);
        foreach ($courses as &$course) {
            if (empty($course['id'])) {
                $course = array();
            }
        }
        $courses = array_filter($courses);

        return $this->render("TopxiaWebBundle:Course:courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users' => $users,
            'mode' => $mode,
        ));
    }

    public function getClassroomUrlAction(Request $request,$courseId, $lessonId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('尚未登入！');
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        if (empty($lesson)) {
            throw $this->createAccessDeniedException('课时不存在！');
        }

        if (empty($lesson['mediaId'])) {
            throw $this->createAccessDeniedException('直播教室不存在！');
        }

        if ($lesson['startTime'] - time() > 7200) {
            throw $this->createAccessDeniedException('直播还没开始!');
        }

        if ($lesson['endTime'] < time()) {
            throw $this->createAccessDeniedException('直播已结束!');
        }

        $params = array(
            'liveId' => $lesson['mediaId'], 
            'provider' => $lesson['liveProvider'],
            'user' => $user['email'],
            'nickname' => $user['nickname'],
        );

        if ($this->getCourseService()->isCourseTeacher($courseId, $user['id'])) {
            $params['role'] = 'teacher';
        } else if ($this->getCourseService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            throw $this->createAccessDeniedException('您不是课程学员，不能参加直播！');
        }

        $client = new EdusohoLiveClient();
        $result = $client->getRoomUrl($params);

        if (empty($result) || isset($result['error'])) {
            return $this->createJsonResponse($result);
        }

        return $this->createJsonResponse(array(
            'url' => $result['url'],
            'param' => isset($result['param']) ? $result['param']:null
        ));
        
    }

    public function entryAction(Request $request,$courseId, $lessonId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        if (empty($lesson)) {
            return $this->createMessageResponse('info', '课时不存在！');
        }

        if (empty($lesson['mediaId'])) {
            return $this->createMessageResponse('info', '直播教室不存在！');
        }

        if ($lesson['startTime'] - time() > 7200) {
            return $this->createMessageResponse('info', '直播还没开始!');
        }

        if ($lesson['endTime'] < time()) {
            return $this->createMessageResponse('info', '直播已结束!');
        }

        $params = array(
            'liveId' => $lesson['mediaId'], 
            'provider' => $lesson['liveProvider'],
            'user' => $user['email'],
            'nickname' => $user['nickname'],
        );

        if ($this->getCourseService()->isCourseTeacher($courseId, $user['id'])) {
            $params['role'] = 'teacher';
        } else if ($this->getCourseService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            return $this->createMessageResponse('info', '您不是课程学员，不能参加直播！');
        }

        if($this->setting("developer.cloud_api_failover", 0)) {
            $client = new EdusohoLiveClient();
            $result = $client->entryLive($params);

            if (empty($result) || isset($result['error'])) {
                return $this->createMessageResponse('info', $result['errorMsg']);
            }
        }

        return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
            'courseId' => $courseId, 
            'lessonId' => $lessonId,
            'lesson' => $lesson,
            'url' => $this->generateUrl('live_classroom_url',array(
                'courseId' => $courseId, 
                'lessonId' => $lessonId
            ))
        ));
    }

    public function verifyAction(Request $request)
    {

        $result = array(
            "code" => "0",
            "msg" => "ok"
        );

        return $this->createJsonResponse($result);
    }

    protected function makeSign($string)
    {
        $secret = $this->container->getParameter('secret');
        return md5($string . $secret);
    }

    public function replayCreateAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $resultList = $this->getCourseService()->generateLessonReplay($courseId,$lessonId);

        if(array_key_exists("error", $resultList)) {
            return $this->createJsonResponse($resultList);
        }
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $lesson["isEnd"] = intval(time()-$lesson["endTime"])>0;
        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:list-item.html.twig', array(
            'course' => $this->getCourseService()->getCourse($courseId),
            'lesson' => $lesson,
        ));
    }

    public function entryReplayAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        
        return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
            'lesson' => $lesson,
            'url' => $this->generateUrl('live_classroom_replay_url',array(
                'courseId' => $courseId, 
                'lessonId' => $lessonId,
                'courseLessonReplayId' => $courseLessonReplayId
            ))
        ));
    }

    public function getReplayUrlAction(Request $request, $courseId, $lessonId, $courseLessonReplayId)
    {
        $course = $this->getCourseService()->tryTakeCourse($courseId);
        $result = $this->getCourseService()->entryReplay($lessonId, $courseLessonReplayId);

        return $this->createJsonResponse(array(
            'url' => $result['url'],
            'param' => isset($result['param']) ? $result['param']:null
        ));
    }

    public function replayManageAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $courseItems = $this->getCourseService()->getCourseItems($course['id']);

        foreach ($courseItems as $key => $item) {
            if($item["itemType"] == "lesson"){
                $item["isEnd"] = intval(time()-$item["endTime"])>0;
                $courseItems[$key] = $item;
            }
        }
        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:index.html.twig', array(
            'course' => $course,
            'items' => $courseItems,
	'default'=> $default
        ));
    }


    protected function getRootCategory($categoryTree, $category)
    {
        $start = false;
        foreach (array_reverse($categoryTree) as $treeCategory) {
            if ($treeCategory['id'] == $category['id']) {
                $start = true;
            }

            if ($start && $treeCategory['depth'] ==1) {
                return $treeCategory;
            }
        }

        return null;
    }

    protected function getSubCategories($categoryTree, $rootCategory)
    {
        $categories = array();

        $start = false;
        foreach ($categoryTree as $treeCategory) {
            
            if ($start && ($treeCategory['depth'] == 1) && ($treeCategory['id'] != $rootCategory['id'])) {
                break;
            }

            if ($treeCategory['id'] == $rootCategory['id']) {
                $start = true;
            }

            if ($start == true) {
                $categories[] = $treeCategory;
            }

        }

        return $categories;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}