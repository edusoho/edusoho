<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\LiveClientFactory;

class LiveCourseController extends BaseController
{
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
            if ($course['status'] != 'published') {
                continue;
            }
            $course['lesson'] = $lesson;
            $recentCourses[] = $course;
        }

        $liveCourses = $this->getCourseService()->searchCourses( array(
            'status' => 'published',
            'type' => 'live'
        ), 'lastest',0, 10 );

        $userIds = array();
        foreach ($liveCourses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:LiveCourse:index.html.twig',array(
            'recentCourses' => $recentCourses,
            'liveCourses' => $liveCourses,
            'users' => $users,
            'paginator' => $paginator,
        ));
	}

    public function ratingCoursesBlockAction()
    {   
        $conditions = array(
            'status' => 'published',
            'type' => 'live',
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
            'mode' => $mode
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

        if ($lesson['startTime'] - time() > 3600) {
            return $this->createMessageResponse('info', '直播还没开始!');
        }

        if ($lesson['endTime'] < time()) {
            return $this->createMessageResponse('info', '直播已结束!');
        }
          
        if ($this->getCourseService()->isCourseTeacher($courseId, $user['id'])) {
            // 老师登录

            $client = LiveClientFactory::createClient();
            $result = $client->startLive($lesson['mediaId']);

            if (empty($result) or isset($result['error'])) {
                return $this->createMessageResponse('info', '进入直播教室失败，请重试！');
            }

            return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
                'lesson' => $lesson,
                'url' => $result['url'],
            ));

        }

        if ($this->getCourseService()->isCourseStudent($courseId, $user['id'])) {

            // http://webinar.vhall.com/appaction.php?module=inituser&pid=***&email=***&name=****&k=****&refer=****
            // $matched = preg_match('/^c(\d+)u(\d+)t(\d+)s(\w+)$/', $k, $matches);
            $now = time();
            $params = array();

            $params['email'] = 'live-' . $user['id'] . '@edusoho.net';
            $params['nickname'] = $user['nickname'];

            $params['sign'] = "c{$lesson['courseId']}u{$user['id']}t{$now}";
            $params['sign'] .= 's' . $this->makeSign($params['sign']);

            $client = LiveClientFactory::createClient();


            $result = $client->entryLive($lesson['mediaId'], $params);

            return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
                'lesson' => $lesson,
                'url' => $result['url'],
            ));

        }

        return $this->createMessageResponse('info', '您不是课程学员，不能参加直播！');
    }


    protected function makeSign($string)
    {
        $secret = $this->container->getParameter('secret');
        return md5($string . $secret);
    }

    public function replayAction(Request $request,$courseId,$lessonId)
    {
        return $this->forward('TopxiaWebBundle:LiveCourse:play', 
        array(
                'courseId'=>$courseId,
                'lessonId'=>$lessonId
            )
        );
    }

    public function replayCreateAction(Request $request, $courseId, $lessonId)
    {
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
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $url = $this->getCourseService()->entryReplay($lessonId, $courseLessonReplayId);
        return $this->render("TopxiaWebBundle:LiveCourse:classroom.html.twig", array(
            'lesson' => $lesson,
            'url' => $url
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

        return $this->render('TopxiaWebBundle:LiveCourseReplayManage:index.html.twig', array(
            'course' => $course,
            'items' => $courseItems
        ));
    }


    private function getRootCategory($categoryTree, $category)
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

    private function getSubCategories($categoryTree, $rootCategory)
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