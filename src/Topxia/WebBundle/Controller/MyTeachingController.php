<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyTeachingController extends BaseController
{
    //已废弃
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
        $user=$this->tryViewTeachingPage();
        $builder=new TeachingPageDataBuilder($user['id']);
        $builder->buildCourseArray();
        $builder->buildManageClassArray();
        $builder->buildThreadArray();
        $builder->buildHomeworkArray();
        $builder->buildTestpaperArray();
        $result=$builder->getResult();
        return $this->render('TopxiaWebBundle:MyTeaching:teaching-k12.html.twig', $result);
    }

    public function teachingCoursesAction(Request $request,$classId)
    {
        $user=$this->tryViewTeachingPage();

        $courses = $this->getCourseService()->findUserTeachCourses($user['id'], 0, PHP_INT_MAX,false);
        $courseCount=count($courses);
        $courses =ArrayToolkit::group($courses,'classId');
        $classIds=array_keys($courses);

        $selectedClass=array();
        if($classId!='all'){
            $selectedClass=$this->getClassesService()->getClass($classId);
            if(empty($selectedClass)){
                throw $this->createNotFoundException('班级不存在');
            }
        }

        if($classId!='all' && !in_array($classId, $classIds)){
            throw $this->createNotFoundException('在该班级无在教课程');
        }
        
        /**如果存在模板课程,则排除这些课程*/
        if(isset($courses[0])){
            $courseCount-=count($courses[0]);
            unset($courses[0]);
        }
        $classes = $this->getClassesService()->findClassesByIds($classIds);

        return $this->render('TopxiaWebBundle:MyTeaching:teaching-courses.html.twig',array(
            'courses'=>$courses,
            'classes'=>$classes,
            'courseCount'=>$courseCount,
            'selectedClass'=>$selectedClass
        ));
    }

	public function threadsAction(Request $request, $type)
	{
		$user=$this->tryViewTeachingPage();

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
        $user=$this->tryViewTeachingPage();

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
        $user=$this->tryViewTeachingPage();
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
        $user=$this->tryViewTeachingPage();
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

        if(empty($lessonId)) {
            $studentIds = array();
            $students = array();
            $lessonId  = -1;
        } else {
           $studentMembers = $this->getClassesService()->findClassStudentMembers($classId);
           $studentMembers = ArrayToolkit::index($studentMembers?:array(), 'userId');
           $studentIds = array_keys($studentMembers);
           $students = $this->getUserService()->findUsersByIds($studentIds);
           $students = ArrayToolkit::index($students?:array(), 'id'); 
        }
        
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
            return $this->render('TopxiaWebBundle:MyTeaching:finished_lesson_tr.html.twig', array(
                'students' => $students,
                'learns' => $learns,
                'questions' => $questions,
                'start' => $start,
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
      
            return $this->render('TopxiaWebBundle:MyTeaching:not_finished_lesson_tr.html.twig', array(
                'students' => $result,
                'start' => $start,
                'total' => $totalCount,
            )); 
        }
    }

    private function tryViewTeachingPage()
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            throw $this->createServiceException('用户不存在或者尚未登录，请先登录');
        }

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }
        return $user;
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

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework.K12HomeworkService');
    }

}

class TeachingPageDataBuilder extends BaseController
{
    private $result= array();
    private $teacherId;
    public function __construct($teacherId)
    {
        $this->teacherId=$teacherId;
    }
    public function buildCourseArray()
    {
        $courseList = $this->getCourseService()->findUserTeachCourses($this->teacherId, 0, PHP_INT_MAX,false);
        $courseCount=count($courseList);
        $courseList =ArrayToolkit::group($courseList,'classId');

        /**如果存在模板课程,则排除这些课程*/
        if(isset($courseList[0])){
            $courseCount-=count($courseList[0]);
            unset($courseList[0]);
        }

        $classIds = array_keys($courseList);
        $classes = $this->getClassesService()->findClassesByIds($classIds);
        $this->result['classes']=$classes;
        $this->result['courseList']=$courseList;
        $this->result['courseCount']=$courseCount;
    }
    public function buildManageClassArray()
    {
        $manageClasses = $this->getClassesService()->getClassesByHeadTeacherId($this->teacherId);
        $this->result['manageClasses']=$manageClasses;
    }
    public function buildThreadArray()
    {
        $courses = $this->getCourseService()->findUserTeachCourses($this->teacherId, 0, PHP_INT_MAX,false);
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
        $this->result['threads']=$threads;
        $this->result['threadCount']=$threadCount;
        $this->result['threadUsers']=$threadUsers;
    }
    public function buildHomeworkArray()
    {
        $status = 'reviewing';
        $courses = $this->getCourseService()->findUserTeachCourses($this->teacherId, 0, PHP_INT_MAX,false);
        $courseIds=ArrayToolkit::column($courses, 'id');
        $reviewingCount=$this->getHomeworkService()->findResultsCountsByCourseIdsAndStatus($courseIds,$status);

        $homeworkResults = $this->getHomeworkService()->findResultsByCourseIdsAndStatus(
            $courseIds,$status,array('usedTime','DESC'),
            0,6
        );
        $homeworkCourses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($homeworkResults,'courseId'));
        $homeworkLessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($homeworkResults,'lessonId'));
        
        $usersIds = ArrayToolkit::column($homeworkResults,'userId');
        $users = $this->getUserService()->findUsersByIds($usersIds);

        $this->result['users']=$users;
        $this->result['homeworkResults']=$homeworkResults;
        $this->result['homeworkCourses']=$homeworkCourses;
        $this->result['homeworkLessons']=$homeworkLessons;
        $this->result['reviewingCount']=$reviewingCount;
    }
    public function buildTestpaperArray()
    {
        $teacherTests = $this->getTestpaperService()->findTeacherTestpapersByTeacherId($this->teacherId);
        $testpaperIds = ArrayToolkit::column($teacherTests, 'id');
        $testpaperCount=$this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($testpaperIds,'reviewing');
        
        $paperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIds($testpaperIds,'reviewing',0,6);
        $testpapers = $this->getTestpaperService()->findTestpapersByIds(ArrayToolkit::column($paperResults, 'testId'));
        $testpaperUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($paperResults, 'userId'));
        //临时增加以下代码，紧急解决复制课程的试卷无法被老师批阅.
        $myCourses = $this->getCourseService()->findUserTeachCourses($this->teacherId, 0, 1000);
        $classIds = ArrayToolkit::column($myCourses, 'classId');
        $userIds = ArrayToolkit::column($testpaperUsers, 'id');
        $classMembers = $this->getClassesService()->findClassMembersByUserIds($userIds);
        foreach ($classMembers as $key => $member) {
            if(!in_array($member['classId'], $classIds)) {
                foreach ($paperResults as $key => $paperResult) {
                    if($member['userId'] == $paperResult['userId']) {
                        unset($paperResults[$key]);
                    }
                }
            }
        }
        $testpaperCount = count($paperResults); 
        //到这里为止.
        $this->result['paperResults']=$paperResults;
        $this->result['testpapers']=$testpapers;
        $this->result['testpaperCount']=$testpaperCount;
        $this->result['testpaperUsers']=$testpaperUsers;
    }
    public function getResult(){
        return $this->result;
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    private function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework.K12HomeworkService');
    }
}