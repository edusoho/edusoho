<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;

class TeacherController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $headTheader = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'headTeacher', 0, 1);
        $assisants = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'assistant', 0, PHP_INT_MAX);
        $members = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'teacher', 0, PHP_INT_MAX);
        $members = array_merge($headTheader, $members, $assisants);
        $members = ArrayToolkit::index($members, 'userId');
        $teacherIds = ArrayToolkit::column($members, 'userId');
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $user = $this->getCurrentUser();

        $Myfollowings = $this->getUserService()->findAllUserFollowing($user['id']);
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
            'member' => $member,
            'members' => $members,
            'Myfollowings' => $Myfollowings,
        ));
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
