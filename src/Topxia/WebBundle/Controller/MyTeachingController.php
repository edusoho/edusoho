<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyTeachingController extends BaseController
{
    
    public function coursesAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = array(
            'userId'=>$user['id']
        );
        
        if($filter == 'normal' ){
            $conditions["parentId"] = 0;
        }

        if($filter == 'classroom'){
            $conditions["parentId_GT"] = 0;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserTeachCourseCount($conditions, false),
            12
        );
        
        $courses = $this->getCourseService()->findUserTeachCourses(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount(),
            false
        );

        $classrooms = array();
        if($filter == 'classroom'){
            $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
            $classrooms = ArrayToolkit::index($classrooms,'courseId');
            foreach ($classrooms as $key => $classroom) {
                $classroomInfo = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
            }
        }

        $courseSetting = $this->getSettingService()->get('course', array());

        return $this->render('TopxiaWebBundle:MyTeaching:teaching.html.twig', array(
            'courses' => $courses,
            'classrooms' => $classrooms,
            'paginator' => $paginator,
            'live_course_enabled' => empty($courseSetting['live_course_enabled']) ? 0 : $courseSetting['live_course_enabled'],
            'filter' => $filter
        ));
    }

    public function classroomsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $classrooms=$this->getClassroomService()->searchMembers(array('roles'=>array('teacher', 'headTeacher', 'assistant'),'userId'=>$user->id),array('createdTime','desc'),0,9999);

        $classroomIds=ArrayToolkit::column($classrooms,'classroomId');

        $classrooms=$this->getClassroomService()->findClassroomsByIds($classroomIds);

        $members=$this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomIds);
        
        foreach ($classrooms as $key => $classroom) {
            
            $courses=$this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $courseIds=ArrayToolkit::column($courses,'courseId');

            $coursesCount=count($courses);

            $classrooms[$key]['coursesCount']=$coursesCount;

            $studentCount=$this->getClassroomService()->searchMemberCount(array('role'=>'student','classroomId'=>$classroom['id'],'startTimeGreaterThan'=>strtotime(date('Y-m-d'))));
            $auditorCount=$this->getClassroomService()->searchMemberCount(array('role'=>'auditor','classroomId'=>$classroom['id'],'startTimeGreaterThan'=>strtotime(date('Y-m-d'))));
            

            $allCount=$studentCount+$auditorCount;

            $classrooms[$key]['allCount']=$allCount;

            $todayTimeStart=strtotime(date("Y-m-d",time()));
            $todayTimeEnd=strtotime(date("Y-m-d",time()+24*3600));
            $todayFinishedLessonNum=$this->getCourseService()->searchLearnCount(array("targetType"=>"classroom","courseIds"=>$courseIds,"startTime"=>$todayTimeStart,"endTime"=>$todayTimeEnd,"status"=>"finished"));

            $threadCount=$this->getThreadService()->searchThreadCount(array('targetType'=>'classroom','targetId'=>$classroom['id'],'type'=>'discussion',"startTime"=>$todayTimeStart,"endTime"=>$todayTimeEnd,"status"=>"open"));

            $classrooms[$key]['threadCount']=$threadCount;

            $classrooms[$key]['todayFinishedLessonNum']=$todayFinishedLessonNum;
        }

        return $this->render('TopxiaWebBundle:MyTeaching:classroom.html.twig', array(
            'classrooms'=>$classrooms,
            'members'=>$members,
            ));
    }

	public function threadsAction(Request $request, $type)
	{
		$user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

		$myTeachingCourseCount = $this->getCourseService()->findUserTeachCourseCount(array('userId'=>$user['id']), true);

        if (empty($myTeachingCourseCount)) {
            return $this->render('TopxiaWebBundle:MyTeaching:threads.html.twig', array(
                'type'=>$type,
                'threadType' => 'course',
                'threads' => array()
            ));
        }

		$myTeachingCourses = $this->getCourseService()->findUserTeachCourses(array('userId'=>$user['id']), 0, $myTeachingCourseCount, true);

		$conditions = array(
			'courseIds' => ArrayToolkit::column($myTeachingCourses, 'id'),
			'type' => $type);

        $paginator = new Paginator(
            $request,
            $this->getCourseThreadService()->searchThreadCountInCourseIds($conditions),
            20
        );

        $threads = $this->getCourseThreadService()->searchThreadInCourseIds(
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
            'type'=>$type,
            'threadType' => 'course',
    	));
	}

    protected function getCourseThreadService()
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

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassroomService() 
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

     protected function getThreadService()
     {
          return $this->getServiceKernel()->createService('Thread.ThreadService');
     }
    
}