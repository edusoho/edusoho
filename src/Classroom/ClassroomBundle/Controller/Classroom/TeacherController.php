<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class TeacherController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $headTheader = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'headTheader', 0, 1);
        $teachers = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'teacher', 0, PHP_INT_MAX);
        $teachers = array_merge($headTheader, $teachers);
        $teacherIds = ArrayToolkit::column($teachers, 'userId');
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $user = $this->getCurrentUser();

        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }

        return $this->render('ClassroomBundle:Classroom\Teacher:list.html.twig', array(
            'layout' => $layout,
            'canLook' => $this->getClassroomService()->canLookClassroom($classroom['id']),
            'classroom' => $classroom,
            'teachers' => $teachers,
            'profiles' => $profiles,
            'member' => $member
        ));
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}
