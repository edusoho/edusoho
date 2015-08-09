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
        $headTeacher = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'headTeacher', 0, 1);
        $assistants = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'assistant', 0, PHP_INT_MAX);
        $studentAssistants = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'studentAssistant', 0, PHP_INT_MAX);
        $members = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'teacher', 0, PHP_INT_MAX);
        $members = array_merge($headTeacher, $members, $assistants,$studentAssistants);
        $members = ArrayToolkit::index($members, 'userId');
        $teacherIds = ArrayToolkit::column($members, 'userId');
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $teachers = $this->sort($teachers, $members);
        $profiles = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $user = $this->getCurrentUser();

        $myfollowings = $this->getUserService()->filterFollowingIds($user['id'], $teacherIds);
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }
        if(!$classroom){
            $classroomDescription = array();
        }
        else{
        $classroomDescription = $classroom['about'];
        $classroomDescription = strip_tags($classroomDescription,'');
        $classroomDescription = preg_replace("/ /","",$classroomDescription);
        } 
        return $this->render('ClassroomBundle:Classroom\Teacher:list.html.twig', array(
            'layout' => $layout,
            'canLook' => $this->getClassroomService()->canLookClassroom($classroom['id']),
            'classroom' => $classroom,
            'teachers' => $teachers,
            'profiles' => $profiles,
            'member' => $member,
            'members' => $members,
            'Myfollowings' => $myfollowings,
            'classroomDescription' => $classroomDescription
        ));
    }
    public function catchIdsAction(Request $request,$classroomId)
    {
        $ids = $request->query->get('ids');
        $ids = explode(',', $ids);
        $ids = array_unique($ids);
        $teacherIds = '';
        foreach ($ids as $id) {
            $isTeacher = $this->getClassroomService()->isClassroomTeacher($classroomId, $id);
            if ($isTeacher) {
                $teacherIds.=$id.',';
            }
        }
        if (!empty($teacherIds)) {
            $teacherIds = substr($teacherIds,0,strlen($teacherIds)-1); ;
        }
        return $this->createJsonResponse($teacherIds);
    }
    private function sort($teachers, $members)
    {
        $newTeachers = array();
        foreach ($members as $key => $member) {
            $newTeachers[$member['userId']] = $teachers[$member['userId']];
        }

        return $newTeachers;
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
