<?php
namespace Classroom\ClassroomBundle\Controller\Classroom;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class TeacherController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        $classroom   = $this->getClassroomService()->getClassroom($classroomId);
        $headTeacher = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'headTeacher', 0, 1);

        $assisants = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'assistant', 0, PHP_INT_MAX);
        $teachers  = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'teacher', 0, PHP_INT_MAX);
        $members   = array_merge($headTeacher, $teachers, $assisants);

        $members = ArrayToolkit::index($members, 'userId');

        $headTeacherIds = ArrayToolkit::column($headTeacher, 'userId');
        $teacherIds     = $this->getClassroomService()->findTeachers($classroomId);
        $assisantIds    = $this->getClassroomService()->findAssistants($classroomId);

        $teacherIds    = array_unique(array_merge($headTeacherIds, $teacherIds, $assisantIds));
        $newteacherIds = array();
        foreach ($teacherIds as $key => $value) {
            $newteacherIds[] = $value;
        }
        $teacherIds = $newteacherIds;
        $teachers   = $this->getUserService()->findUsersByIds($teacherIds);
        $users      = array();
        foreach ($teacherIds as $key => $teacherId) {
            $users[$key] = $teachers[$teacherId];
        }
        $profiles = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $user     = $this->getCurrentUser();

        $classroomSetting = $this->setting('classroom', array());
        $classroomName    = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

        $myfollowings = $this->getUserService()->filterFollowingIds($user['id'], $teacherIds);
        $member       = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
        }
        $layout = 'ClassroomBundle:Classroom:layout.html.twig';
        if ($member && !$member['locked']) {
            $layout = 'ClassroomBundle:Classroom:join-layout.html.twig';
        }
        if (!$classroom) {
            $classroomDescription = array();
        } else {
            $classroomDescription = $classroom['about'];
            $classroomDescription = strip_tags($classroomDescription, '');
            $classroomDescription = preg_replace("/ /", "", $classroomDescription);
        }
        return $this->render('ClassroomBundle:Classroom\Teacher:list.html.twig', array(
            'layout'               => $layout,
            'canLook'              => $this->getClassroomService()->canLookClassroom($classroom['id']),
            'classroom'            => $classroom,
            'teachers'             => $users,
            'profiles'             => $profiles,
            'member'               => $member,
            'members'              => $members,
            'Myfollowings'         => $myfollowings,
            'classroomDescription' => $classroomDescription
        ));
    }

    public function catchIdsAction(Request $request, $classroomId)
    {
        $ids        = $request->query->get('ids');
        $ids        = explode(',', $ids);
        $ids        = array_unique($ids);
        $teacherIds = '';
        foreach ($ids as $id) {
            $isTeacher = $this->getClassroomService()->isClassroomTeacher($classroomId, $id);
            if ($isTeacher) {
                $teacherIds .= $id.',';
            }
        }
        if (!empty($teacherIds)) {
            $teacherIds = substr($teacherIds, 0, strlen($teacherIds) - 1);
        }
        return $this->createJsonResponse($teacherIds);
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
