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

        $group = $this->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            $categories = array();
        } else {
            $categories = $this->getCategoryService()->findGroupRootCategories($group['code']);
        }

        $conditions = array(
            'status' => 'published',
            'type' => 'live'
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
        
        return $this->render('TopxiaWebBundle:LiveCourse:index.html.twig',array(
            'rootCategories' => $categories,
            'newCourses' => $newCourses,
            'recentlessons' => $recentlessons,
            'recentCourses' => $recentCourses,
            'users' => $users,
            'tomorrow' => $tomorrow,
            'category' => array('id' => null,'parentId' => null)
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
            $category = array('id' => null,'parentId' => null);
        }

        $group = $this->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            $categories = array();
            $rootCategories = array();
        } else {
            $categories = $this->getCategoryService()->getCategoryTree($group['id']);
            $rootCategories = $this->getCategoryService()->findGroupRootCategories($group['code']);
        }

        $rootCategory = $this->getRootCategory($categories,$category);

        $subCategories = $this->getSubCategories($categories, $rootCategory);

        $date = $request->query->get('date', 'today');

        $conditions = array(
            'status' => 'published',
            'categoryId' => $category['id'],
            'type' => 'live'
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

        return $this->render('TopxiaWebBundle:LiveCourse:list.html.twig',array(
            'date' => $date,
            'category' => $category,
            'categories' => $categories,
            'rootCategories' => $rootCategories,
            'subCategories' => $subCategories,
            'paginator' => $paginator,
            'courses' => $courses,
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
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:LiveCourse:live-courses-block-{$view}.html.twig", array(
            'courses' => $courses,
            'users' => $users,
            'mode' => $mode,
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