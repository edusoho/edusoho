<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class TeacherController extends BaseController
{
    public function listAction($classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $headTeacher = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'headTeacher', 0, 1);

        $assisants = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'assistant', 0, PHP_INT_MAX);
        $teachers = $this->getClassroomService()->findClassroomMembersByRole($classroomId, 'teacher', 0, PHP_INT_MAX);
        $members = array_merge($headTeacher, $teachers, $assisants);

        $members = ArrayToolkit::index($members, 'userId');

        $teacherIds = $this->getClassroomService()->findTeachers($classroomId);
        $assisantIds = $this->getClassroomService()->findAssistants($classroomId);

        $teacherIds = array_unique(array_merge($teacherIds, $assisantIds));
        $newTeacherIds = array();
        foreach ($teacherIds as $key => $value) {
            $newTeacherIds[] = $value;
        }
        $teacherIds = $newTeacherIds;
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);
        $users = array();
        foreach ($teacherIds as $key => $teacherId) {
            $users[$key] = $teachers[$teacherId];
        }
        $profiles = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $user = $this->getCurrentUser();

        $classroomSetting = $this->setting('classroom', array());
        $classroomName = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';

        $myfollowings = $this->getUserService()->filterFollowingIds($user['id'], $teacherIds);
        $member = $user['id'] ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;
        $canLook = $this->getClassroomService()->canLookClassroom($classroom['id']);
        if (!$canLook) {
            return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomName}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
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

        return $this->render('classroom/teacher/list.html.twig', array(
            'layout' => $layout,
            'canLook' => $canLook,
            'classroom' => $classroom,
            'teachers' => $users,
            'profiles' => $profiles,
            'member' => $member,
            'members' => $members,
            'Myfollowings' => $myfollowings,
            'classroomDescription' => $classroomDescription,
        ));
    }

    public function catchIdsAction(Request $request, $classroomId)
    {
        $ids = $request->query->get('ids');
        $ids = explode(',', $ids);
        $ids = array_unique($ids);
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

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
