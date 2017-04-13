<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class CourseNoteController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $classroomCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, 'id');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $user = $this->getCurrentUser();

        $classroomSetting = $this->setting('classroom', array());
        $classroomName = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

        $member = $user->isLogin() ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $canLook = $this->getClassroomService()->canLookClassroom($classroom['id']);
        if (!$canLook) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}11111，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $layout = 'classroom/layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'classroom/join-layout.html.twig';
        }
        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace('/ /', '', $classroomDescription);
        }

        return $this->render('classroom/course/notes-list.html.twig', array(
            'layout' => $layout,
            'filters' => $this->getNoteSearchFilters($request),
            'canLook' => $canLook,
            'classroom' => $classroom,
            'courseIds' => $courseIds,
            'courses' => $courses,
            'member' => $member,
            'classroomDescription' => $classroomDescription,
            'courseSets' => $courseSets,
        ));
    }

    private function getNoteSearchFilters($request)
    {
        $filters = array();

        $filters['courseId'] = $request->query->get('courseId', '');
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('latest', 'likeNum'))) {
            $filters['sort'] = 'latest';
        }

        return $filters;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
