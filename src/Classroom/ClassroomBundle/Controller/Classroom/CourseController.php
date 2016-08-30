<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class CourseController extends BaseController
{
    public function pickAction(Request $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $actviteCourses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);

        $excludeIds = ArrayToolkit::column($actviteCourses, 'parentId');
        $conditions = array(
            'status'     => 'published',
            'parentId'   => 0,
            'excludeIds' => $excludeIds
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

        $userIds = array();
        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("ClassroomBundle:ClassroomManage/Course:course-pick-modal.html.twig", array(
            'users'       => $users,
            'courses'     => $courses,
            'classroomId' => $classroomId,
            'paginator'   => $paginator
        ));
    }

    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $previewAs = "";

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        $courses       = $this->getClassroomService()->findActiveCoursesByClassroomId($classroomId);
        $currentUser   = $this->getUserService()->getCurrentUser();
        $courseMembers = array();
        $teachers      = array();

        foreach ($courses as &$course) {
            $courseMembers[$course['id']] = $this->getCourseService()->getCourseMember($course['id'], $currentUser->id);

            $course['teachers']      = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);
            $teachers[$course['id']] = $course['teachers'];
        }

        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            $classroomName = $this->setting('classroom.name', '班级');
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }

        $canManageClassroom = $this->getClassroomService()->canManageClassroom($classroomId);
        if ($request->query->get('previewAs') && $canManageClassroom) {
            $previewAs = $request->query->get('previewAs');
        }

        $member = $this->previewAsMember($previewAs, $member, $classroom);

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member["locked"]) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }
        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace("/ /", "", $classroomDescription);
        }
        return $this->render("ClassroomBundle:Classroom/Course:list.html.twig", array(
            'classroom'            => $classroom,
            'member'               => $member,
            'teachers'             => $teachers,
            'courses'              => $courses,
            'courseMembers'        => $courseMembers,
            'layout'               => $layout,
            'classroomDescription' => $classroomDescription
        ));
    }

    public function searchAction(Request $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $key = $request->request->get("key");

        $conditions             = array("title" => $key);
        $conditions['status']   = 'published';
        $conditions['parentId'] = 0;
        $courses                = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            0,
            5
        );

        $userIds = array();
        foreach ($courses as &$course) {
            $course['tags'] = $this->getTagService()->findTagsByIds($course['tags']);
            $userIds        = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:Course:course-select-list.html.twig', array(
            'users'   => $users,
            'courses' => $courses
        ));
    }

    private function previewAsMember($previewAs, $member, $classroom)
    {
        $user = $this->getCurrentUser();

        if (in_array($previewAs, array('guest', 'auditor', 'member'))) {
            if ($previewAs == 'guest') {
                return;
            }

            $member = array(
                'id'          => 0,
                'classroomId' => $classroom['id'],
                'userId'      => $user['id'],
                'orderId'     => 0,
                'levelId'     => 0,
                'noteNum'     => 0,
                'threadNum'   => 0,
                'remark'      => '',
                'role'        => array('auditor'),
                'locked'      => 0,
                'createdTime' => 0
            );

            if ($previewAs == 'member') {
                $member['role'] = array('member');
            }
        }

        return $member;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getClassroomReviewService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomReviewService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
