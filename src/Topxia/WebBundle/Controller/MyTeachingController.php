<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyTeachingController extends BaseController
{
    
    public function coursesAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserTeachCourseCount($user['id'], false),
            12
        );
        
        $courses = $this->getCourseService()->findUserTeachCourses(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount(),
            false
        );

        $courseSetting = $this->getSettingService()->get('course', array());

        return $this->render('TopxiaWebBundle:MyTeaching:teaching.html.twig', array(
            'courses'=>$courses,
            'paginator' => $paginator,
            'live_course_enabled' => empty($courseSetting['live_course_enabled']) ? 0 : $courseSetting['live_course_enabled']
        ));
    }

    public function teachingAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }
        
        $courses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, PHP_INT_MAX,false);
        $courseCount=count($courses);
        $courseList =ArrayToolkit::group($courses,'classId');
        $classIds = array_keys($courseList);
        $classes = $this->getClassesService()->findClassesByIds($classIds);

        $manageClasses = $this->getClassesService()->getClassesByHeadTeacherId($user['id']);

        $courseIds=ArrayToolkit::column($courses, 'id');
        if(empty($courseIds)){
            $threadCount=0;
            $threads=array();
            $threadUsers=array();
        }else{
            $conditions = array(
                'courseIds' => $courseIds,
                'type' => 'question'
            );
            // $threadCount= $this->getThreadService()->searchThreadCountInCourseIds($conditions);
            $threads = $this->getThreadService()->searchThreadInCourseIds($conditions,'createdNotStick',0,6);
            $threadList=array();
            foreach ($threads as $thread) {
                $elitePosts=$this->getThreadService()->findThreadElitePosts($thread['courseId'], $thread['id'], 0, PHP_INT_MAX);
                if(count($elitePosts)==0){
                    $threadList[]=$thread;
                }
            }
            $threads=$threadList;
            $threadCount=count($threads);
            $threadUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        }

        $teacherTests = $this->getTestpaperService()->findTeacherTestpapersByTeacherId($user['id']);
        $testpaperIds = ArrayToolkit::column($teacherTests, 'id');
        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);
        $testpaperCount=$this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($testpaperIds,'reviewing');
        $paperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIds($testpaperIds,'reviewing',0,6);
        $testpapers = $this->getTestpaperService()->findTestpapersByIds(ArrayToolkit::column($paperResults, 'testId'));
        $testpaperUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($paperResults, 'userId'));
        return $this->render('TopxiaWebBundle:MyTeaching:teaching-k12.html.twig', array(
            'classes' => $classes,
            'courseList' => $courseList,
            'courseCount'=>$courseCount,
            'manageClasses'=>$manageClasses,
            'threads'=>$threads,
            'threadCount'=>$threadCount,
            'threadUsers'=>$threadUsers,
            'paperResults'=>$paperResults,
            'testpapers'=>$testpapers,
            'testpaperCount'=>$testpaperCount,
            'testpaperUsers'=>$testpaperUsers
        ));
    }

    public function teachingCoursesAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $courses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, PHP_INT_MAX,false);
        $courseCount=count($courses);
        $courses =ArrayToolkit::group($courses,'classId');
        
        $classes = $this->getClassesService()->findClassesByIds(array_keys($courses));

        return $this->render('TopxiaWebBundle:MyTeaching:teaching-courses.html.twig',array(
            'courses'=>$courses,
            'classes'=>$classes,
            'courseCount'=>$courseCount
        ));
    }

	public function threadsAction(Request $request, $type)
	{
		$user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

		$myTeachingCourseCount = $this->getCourseService()->findUserTeachCourseCount($user['id'], true);

        if (empty($myTeachingCourseCount)) {
            return $this->render('TopxiaWebBundle:MyTeaching:threads.html.twig', array(
                'type'=>$type,
                'threads' => array()
            ));
        }

		$myTeachingCourses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, $myTeachingCourseCount, true);

		$conditions = array(
			'courseIds' => ArrayToolkit::column($myTeachingCourses, 'id'),
			'type' => $type);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCountInCourseIds($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreadInCourseIds(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($threads, 'lessonId'));

    	return $this->render('TopxiaWebBundle:MyTeaching:threads.html.twig', array(
    		'paginator' => $paginator,
            'threads' => $threads,
            'users'=> $users,
            'courses' => $courses,
            'lessons' => $lessons,
            'type'=>$type
    	));
	}

    public function myTasksAction(Request $request)
    {   
        $user = $this->getCurrentUser();
        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $classId = $request->query->get('classId');
        $classId = empty($classId) ? 0 : $classId;
        $date = $request->query->get('date');
        $date = empty($date) ? date('Y-m-d') : $date;
        $teachCourses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, PHP_INT_MAX,false);
        $courseCount = count($teachCourses);
        $courseList = ArrayToolkit::group($teachCourses,'classId');
        $classIds = array_keys($courseList);
        $teachClasses = $this->getClassesService()->findClassesByIds($classIds);

        return $this->render('TopxiaWebBundle:MyTeaching:mytasks.html.twig', array(
            'teachClasses' => $teachClasses,
            'classId' => $classId,
            'date' => $date,
            ));
    }

    public function getLessonsAction(Request $request, $classId, $date)
    {
        $user = $this->getCurrentUser();
        if(!$user->isTeacher()) {
            return $this->createNotFoundException('您不是老师，不能查看此页面！');
        }
        $date = empty($date) ? date('Ymd') : str_replace(array('-','/'), '', $date);

        $result = $this->getScheduleService()->findOneDaySchedulesByUserId($classId, $user['id'], $date);
        
        return $this->render('TopxiaWebBundle:MyTeaching:mytasks-carousel.html.twig', array(
            'courses' => $result['courses'],
            'lessons' => $result['lessons'],
            'schedules' => $result['schedules'],
            'classes' => $result['classes'],
            ));
    }

    public function getFinshedLessonStudentsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if(!$user->isTeacher()) {
            return $this->createNotFoundException('您不是老师，不能查看此页面！');
        }
        $lessonId = $request->query->get('lessonId');
        $start = $request->query->get('start');
        $limit = $request->query->get('limit');
        $type = $request->query->get('type');
        $classId = $request->query->get('classId');

        if($classId == 0 && $lessonId) {
            $lesson = $this->getCourseService()->getLesson($lessonId);
            $course = $this->getCourseService()->getCourse($lesson['courseId']);
            $classId = $course['classId'];
        }
        
        $studentMembers = $this->getClassesService()->findClassStudentMembers($classId);
        $studentMembers = ArrayToolkit::index($studentMembers?:array(), 'userId');
        $studentIds = array_keys($studentMembers);    
        $students = $this->getUserService()->findUsersByIds($studentIds);
        $students = ArrayToolkit::index($students?:array(), 'id');

        $conditions = array(
            'lessonId' => $lessonId,
            'status' => 'finished',
            'userIds' => $studentIds
            );
        $totalCount = $this->getCourseService()->searchLearnCount($conditions);
        $orderby = array('finishedTime', 'ASC');
        
        if($type == 'finished') {
            $learns = $this->getCourseService()->searchLearns($conditions, $orderby, $start, $limit);
            $conditions = array(
                'lessonId' => $lessonId,
                'type' => 'question',
                'userIds' => $studentIds
                );
            $courseThread = $this->getThreadService()->searchThreads($conditions, 'createdTimeASC', 0, 10000);
            $questions = ArrayToolkit::index($courseThread, 'userId');
            $more = $totalCount>$start+$limit ? true:false;
            return $this->render('TopxiaWebBundle:MyTeaching:finished_lesson_tr.html.twig', array(
                'students' => $students,
                'learns' => $learns,
                'questions' => $questions,
                'start' => $start,
                'more' => $more,
                ));
        } else {
            $learns = $this->getCourseService()->searchLearns($conditions, $orderby, 0, $totalCount);
            $finishedIds = array_keys(ArrayToolkit::index($learns?:array(), 'userId')) ;
            $notFinishedIds = array_diff($studentIds, $finishedIds);
            $limitIds = array_slice($notFinishedIds, $start, $limit, true);
            $result = array();
            foreach ($limitIds as $value) {
                $result[] = $students[$value]; 
            }
      
            $more = (count($studentIds) - count($finishedIds) > $start+$limit) ? true:false;
            return $this->render('TopxiaWebBundle:MyTeaching:not_finished_lesson_tr.html.twig', array(
                'students' => $result,
                'start' => $start,
                'total' => $totalCount,
                'more' => $more,
                )); 
        }
        

    }

	protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getScheduleService()
    {
        return $this->getServiceKernel()->createService('Schedule.ScheduleService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

}