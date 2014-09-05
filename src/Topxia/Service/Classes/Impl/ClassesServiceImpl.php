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
        $conditions = array_filter($conditions);   
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
        $this->addClassMember($classMember);
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

    public function checkPermission($name, $classId)
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
                'id' => null,
                'userId' => $user['id'],
                'classId' => $class['id'],
                'role' => 'ADMIN',
            );
        }

        if (empty($member)) {
            return false;
        }

        $permissionRoles = array(
            'view' => array('STUDENT', 'HEAD_TEACHER', 'ADMIN'),
            'manage' => array('HEAD_TEACHER', 'ADMIN'),
        );

        if (!array_key_exists($name, $permissionRoles)) {
            return false;
        }

        return in_array($member['role'], $permissionRoles[$name]);
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
                'id' => null,
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
                'id' => null,
                'userId' => $user['id'],
                'classId' => $class['id'],
                'role' => 'ADMIN',
            );
        } else {
            if (!in_array($member['role'], array('HEAD_TEACHER'))) {
                return false;
            }
        }

        return array($class, $member);
    }

    public function canMemberManageClass($class, $member)
    {
        if (empty($class) or empty($member)) {
            return false;
        }

        if (!in_array($member['role'], array('HEAD_TEACHER', 'ADMIN'))) {
            return false;
        }

        return true;
    }

    public function editClass($fields, $id)
    {
        $class = $this->getClassesDao()->editClass($fields, $id);
        $conditions = array(
            'classId' => $class['id'],
            'role' => 'HEAD_TEACHER'
            );

        $oldClassMember = $this->searchClassMembers(
            $conditions,
            array('id','DESC'),
            0,
            1);

        if($oldClassMember[0]['userId'] != $class['headTeacherId']) {
            $this->updateClassMember(array('userId'=>$class['headTeacherId']), $oldClassMember[0]['id']);
        }
        
        return $class;
    }

    public function updateClassStudentNum($num,$id){
        $this->getClassesDao()->updateClassStudentNum($num,$id);
    }

    public function deleteClass($id)
    {
        return $this->getClassesDao()->deleteClass($id);
    }

    public function getMemberByUserIdAndClassId($userId,$classId)
    {
        return $this->getClassMemberDao()->getMemberByUserIdAndClassId($userId, $classId);
    }

    public function findClassStudentMembers($classId)
    {
        return $this->getClassMemberDao()->findMembersByClassIdAndRole($classId, 'STUDENT');
    }

    public function searchClassMembers(array $conditions, array $oderBy, $start, $limit)
    {
        return $this->getClassMemberDao()->searchClassMembers($conditions, $oderBy, $start, $limit);
    }

    public function searchClassMemberCount(array $conditions)
    {
        return $this->getClassMemberDao()->searchClassMemberCount($conditions);
    }

    public function addClassMember(array $classMember){
        return $this->getClassMemberDao()->addClassMember($classMember);
    }

    public function deleteClassMemberByUserId($userId){
        $this->getClassMemberDao()->deleteClassMemberByUserId($userId);
    }

    public function updateClassMember(array $fields, $id)
    {
        return $this->getClassMemberDao()->updateClassMember($fields, $id);
    }

    private function getClassesDao ()
    {
        return $this->createDao('Classes.ClassesDao');
    }

    private function getClassMemberDao ()
    {
        return $this->createDao('Classes.ClassMemberDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}