<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/15
 * Time: 18:42
 */

namespace Custom\WebBundle\Controller\Classroom;
use Symfony\Component\HttpFoundation\Request;
use Classroom\ClassroomBundle\Controller\Classroom\CourseController as BaseCourseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseController extends BaseCourseController
{
    public function pickAction(Request $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $actviteCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        $excludeIds = ArrayToolkit::column($actviteCourses, 'parentId');
        $conditions = array(
            'status' => 'published',
            'parentId' => 0,
            'excludeIds' => $excludeIds,
            'rootId' => 0
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount($conditions),
            5
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseIds = ArrayToolkit::column($courses, 'id');
        $userIds = array();
        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("ClassroomBundle:ClassroomManage/Course:course-pick-modal.html.twig", array(
            'users' => $users,
            'courses' => $courses,
            'classroomId' => $classroomId,
            'paginator' => $paginator,
        ));
    }

    public function searchAction(Request $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $key = $request->request->get("key");

        $conditions = array( "title" => $key );
        $conditions['status'] = 'published';
        $conditions['parentId'] = 0;
        $conditions['rootId'] = 0;
        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            0,
            5
        );

        $courseIds = ArrayToolkit::column($courses, 'id');

        $userIds = array();
        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course:course-select-list.html.twig', array(
            'users' => $users,
            'courses' => $courses,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}