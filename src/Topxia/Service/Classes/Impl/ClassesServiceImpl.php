<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Classes\ClassesService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class ClassesServiceImpl extends BaseService implements ClassesService
{
    public function getClass($id)
    {
        return $this->getClassesDao()->getClass($id);
    }


    public function findClassesByIds(array $ids)
    {
        $classes=$this->getClassesDao()->findClassesByIds($ids);
        return ArrayToolkit::index($classes, 'id');
    }

    public function searchClasses($conditions, $sort = array(), $start, $limit) 
    {
        return $this->getClassesDao()->searchClasses($conditions, $sort, $start, $limit);
    }

    public function searchClassCount($conditions)
    {
        return $this->getClassesDao()->searchClassCount($conditions);
    }

    public function createClass($class)
    {
        $class = $this->getClassesDao()->createClass($class);
        $classMember['classId'] = $class['id'];
        $classMember['userId'] = $class['headTeacherId'];
        $classMember['role'] = 'HEAD_TEACHER';
        $classMember['createdTime'] = time();
        $this->getClassMemberService()->addClassMember($classMember);
        return $class;
    }

    public function getStudentClass($userId)
    {
        $member = $this->getClassMemberDao()->getMemberByUserId($userId);
        if (empty($member)) {
            return null;
        }

        return $this->getClass($member['classId']);
    }

    public function getClassHeadTeacher($classId)
    {
        $member = $this->getClassMemberDao()->getMemberByClassIdAndRole($classId, 'HEAD_TEACHER');
        if (empty($member)) {
            return null;
        }

        $user = $this->getUserService()->getUser($member['userId']);
        $profile = $this->getUserService()->getUserProfile($member['userId']);

        if (empty($user) or empty($profile)) {
            return null;
        }

        return array_merge($user, $profile);
    }

    public function canViewClass($classId)
    {
        $class = $this->getClass($classId);
        if (empty($class)) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (empty($user)) {
            return false;
        }

        $member = $this->getClassMemberDao()->getMemberByUserIdAndClassId($user['id'], $classId);

        if ($user->isAdmin()) {
            $member = array(
                'userId' => $user['id'],
                'classId' => $class['id'],
                'role' => 'ADMIN',
            );
        } else {
            if (!in_array($member['role'], array('HEAD_TEACHER', 'STUDENT'))) {
                return false;
            }
        }

        return array($class, $member);
    }

    public function canManageClass($classId)
    {
        $user = $this->getCurrentUser();
        if (empty($user)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $member = $this->getClassMemberDao()->getMemberByUserIdAndClassId($user['id'], $classId);

        if (in_array($member['role'], array('HEAD_TEACHER', 'STUDENT'))) {
            return true;
        }

        return false;
    }

    public function editClass($fields, $id)
    {
        $class = $this->getClassesDao()->editClass($fields, $id);
        $conditions = array(
            'classId' => $class['id'],
            'role' => 'HEAD_TEACHER'
            );

        $oldClassMember = $this->getClassMemberService()->searchClassMembers(
            $conditions,
            array('id','DESC'),
            0,
            1);

        if($oldClassMember[0]['userId'] != $class['headTeacherId']) {
            $this->getClassMemberService()
            ->updateClassMember(array('userId'=>$class['headTeacherId']), $oldClassMember[0]['id']);
        }
        
        return $class;
    }

    public function deleteClass($id)
    {
        return $this->getClassesDao()->deleteClass($id);
    }
    private function getClassesDao ()
    {
        return $this->createDao('Classes.ClassesDao');
    }

    private function getClassMemberDao ()
    {
        return $this->createDao('Classes.ClassMemberDao');
    }

    private function getClassMemberService()
    {
        return $this->createService('Classes.ClassMemberService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}