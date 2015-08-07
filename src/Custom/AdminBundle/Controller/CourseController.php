<?php
namespace Custom\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\AdminBundle\Controller\CourseController as BaseCourseController;

class CourseController extends BaseCourseController
{

    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->query->all();
        if($filter == 'normal' ){
            $conditions["parentId"] = 0;
        }

        if($filter == 'classroom'){
            $conditions["parentId_GT"] = 0;
        }

        if(isset($conditions["categoryId"]) && $conditions["categoryId"]==""){
            unset($conditions["categoryId"]);
        }
        if(isset($conditions["status"]) && $conditions["status"]==""){
            unset($conditions["status"]);
        }
        if(isset($conditions["title"]) && $conditions["title"]==""){
            unset($conditions["title"]);
        }
        if(isset($conditions["creator"]) && $conditions["creator"]==""){
            unset($conditions["creator"]);
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
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

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $courseSetting = $this->getSettingService()->get('course', array());
        if (!isset($courseSetting['live_course_enabled'])) {
            $courseSetting['live_course_enabled'] = "";
        }

        $default = $this->getSettingService()->get('default', array());

        return $this->render('CustomAdminBundle:Course:index.html.twig', array(
            'conditions' => $conditions,
            'courses' => $courses,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator,
            'liveSetEnabled' => $courseSetting['live_course_enabled'],
            'default' => $default,
            'classrooms' => $classrooms,
            'filter' => $filter
        ));
    }

    public function nextRoundAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);

        return $this->render('CustomAdminBundle:Course:next-round.html.twig', array(
            'course' => $course,
        ));
    }

    public function roundingAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $conditions = $request->request->all();
        $course['startTime'] = strtotime($conditions['startTime']);
        $course['endTime'] = strtotime($conditions['endTime']);

        $this->getNextRoundService()->rounding($course);

        return $this->redirect($this->generateUrl('admin_course'));
    }

    protected function getNextRoundService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.NextRoundService');
    }
}
