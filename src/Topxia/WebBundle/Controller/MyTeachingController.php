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

    public function classroomsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $classrooms=array();
        $teacherClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'teacher','userId'=>$user->id),array('createdTime','desc'),0,9999);
        $headerTeacherClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'headerTeacher','userId'=>$user->id),array('createdTime','desc'),0,9999);

        $classrooms=array_merge($teacherClassrooms,$headerTeacherClassrooms);

        $classroomIds=ArrayToolkit::column($classrooms,'classroomId');

        $classrooms=$this->getClassroomService()->findClassroomsByIds($classroomIds);

        $members=$this->getClassroomService()->findMembersByClassroomIds($classroomIds);
        
        foreach ($classrooms as $key => $classroom) {
            
            $courses=$this->getClassroomService()->getAllCourses($classroom['id']);
            $courseIds=ArrayToolkit::column($courses,'courseId');

            $coursesCount=count($courses);

            $classrooms[$key]['coursesCount']=$coursesCount;

            $studentCount=$this->getClassroomService()->searchMemberCount(array('role'=>'student','classroomId'=>$classroom['id'],'startTimeGreaterThan'=>strtotime(date('Y-m-d'))));
            $auditorCount=$this->getClassroomService()->searchMemberCount(array('role'=>'auditor','classroomId'=>$classroom['id'],'startTimeGreaterThan'=>strtotime(date('Y-m-d'))));
            
            $allCount=$studentCount+$auditorCount;

            $classrooms[$key]['allCount']=$allCount;
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

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassroomService() 
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}