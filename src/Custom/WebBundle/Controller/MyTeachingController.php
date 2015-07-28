<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\MyTeachingController as MyBaseTeachingController;

class MyTeachingController extends MyBaseTeachingController
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

        return $this->render('CustomWebBundle:MyTeaching:teaching.html.twig', array(
            'courses' => $courses,
            'classrooms' => $classrooms,
            'paginator' => $paginator,
            'live_course_enabled' => empty($courseSetting['live_course_enabled']) ? 0 : $courseSetting['live_course_enabled'],
            'filter' => $filter
        ));
    }
}