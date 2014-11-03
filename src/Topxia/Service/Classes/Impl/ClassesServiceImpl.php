<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Classes\ClassesService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class ClassesServiceImpl extends BaseService implements ClassesService
{
    protected   $roles = array('PARENT' => 1, 'STUDENT' => 2, 'TEACHER' => 3, 'HEAD_TEACHER' => 4);
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
        $conditions = array_filter($conditions);
        return $this->getClassesDao()->searchClassCount($conditions);
    }

    public function createClass($class)
    {
        $class = $this->getClassesDao()->createClass($class);
        $newClassMember = array();
        $newClassMember['classId'] = $classId;
        $newClassMember['userId'] = $userId;
        $newClassMember['role'] = 'HEAD_TEACHER';
        $newClassMember['createdTime'] = time();
        $classDao->addClassMember($newClassMember);
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
        $class = $this->getClass($classId);
        if (empty($class)) {
            return null;
        }

        $user = $this->getUserService()->getUser($class['headTeacherId']);
        $profile = $this->getUserService()->getUserProfile($class['headTeacherId']);

        if (empty($user) or empty($profile)) {
            return null;
        }

        return array_merge($profile, $user);
    }

    public function getClassesByHeadTeacherId($headTeacherId)
    {
        return $this->getClassesDao()->findClassesByHeadTeacherId($headTeacherId);
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
            'view' => array('STUDENT', 'TEACHER', 'HEAD_TEACHER', 'ADMIN'),
            'manage' => array('HEAD_TEACHER', 'ADMIN'),
            'manageSchedule' => array('TEACHER', 'HEAD_TEACHER', 'ADMIN'),
            'viewSchedule' => array('STUDENT', 'TEACHER', 'HEAD_TEACHER', 'ADMIN', 'PARENT'),
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
        $classMember = $classDao->getMemberByClassIdAndRole($classId, 'HEAD_TEACHER');
        $this->updateClassMember(array('userId'=>$userId), $classMember['id']);
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

    public function getStudentMemberByUserIdAndClassId($userId ,$classId)
    {
        return $this->getClassMemberDao()->getStudentMemberByUserIdAndClassId($userId, $classId);
    }

    public function findStudentMembersByUserIdsAndClassId($userIds, $classId)
    {
        return $this->getClassMemberDao()->findStudentMembersByUserIdsAndClassId($userIds, $classId);
    }

    public function findClassStudentMembers($classId)
    {
        return $this->getClassMemberDao()->findMembersByClassIdAndRole($classId, 'STUDENT');
    }

    public function findClassMemberByUserNumber($number, $classId)
    {
        $user = $this->getUserService()->getUserByNumber($number);
        if(empty($user)) {
            return null;
        } else {
            return $this->getClassMemberDao()->getStudentMemberByUserIdAndClassId($user['id'], $classId);
        }
    }

    public function findClassByUserNumber($number)
    {
        $user = $this->getUserService()->getUserByNumber($number);
        if(empty($user)) {
            return null;
        } else {
            return $this->getStudentClass($user['id']);
        }
    }

    public function findClassMembersByUserIds(array $userIds)
    {
        return $this->getClassMemberDao()->findClassMembersByUserIds($userIds);
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

    public function addRoleToClass($userId, $classId, $role)
    {

        $member = $this->getMemberByUserIdAndClassId($userId, $classId);
        if (empty($member)) {
            $newClassMember = array();
            $newClassMember['classId'] = $classId;
            $newClassMember['userId'] = $userId;
            $newClassMember['role'] = $role;
            $newClassMember['createdTime'] = time();
            $member = $this->addClassMember($newClassMember);
        } elseif ($this->roles[$role] > $this->roles[$member['role']]) {
            $member = $this->updateClassMember(array('role' => $role), $member['id']); 
        }
        return $member;
    }

    public function deleteClassMemberByUserId($userId){
        $this->getClassMemberDao()->deleteClassMemberByUserId($userId);
    }

    public function updateClassMember(array $fields, $id)
    {
        return $this->getClassMemberDao()->updateClassMember($fields, $id);
    }

    public function importStudents($classId, array $userIds){
        foreach ($userIds as $userId) 
        {
            $classMember['classId']=$classId;
            $classMember['userId']=$userId;
            $classMember['role']='STUDENT';
            $classMember['title']='';
            $classMember['createdTime']=time();
            $this->addClassMember($classMember);
        }
        $this->updateClassStudentNum(count($userIds),$classId);
    }

    public function importParents($classId, array $userIds)
    {
        foreach ($userIds as $userId) 
        {
            $member=$this->getStudentClass($userId);
            if(empty($member)){
                $classMember['classId']=$classId;
                $classMember['userId']=$userId;
                $classMember['role']='PARENT';
                $classMember['title']='';
                $classMember['createdTime']=time();
                $this->addClassMember($classMember);
            }
        }
    }

    public function refreashStudentRank($userId, $classId)
    {
        $studentMembers = $this->findClassStudentMembers($classId);
        $studentIds = ArrayToolkit::column($studentMembers, 'userId');
        $students = $this->getUserService()->findUsersByIdsAndOrder($studentIds, array('point', 'DESC'));
        $student = array();
        $currentRank = 0;
        $index = 1;
        foreach ($students as $item) {
            if($item['id'] == $userId) {
                $student = $item;
                $currentRank = $index;
                break;
            }
            $index++;
        }

        $studentMember = $this->getMemberByUserIdAndClassId($userId, $classId);
        $newMember = array();
        $newMember['currentRank'] = $currentRank;
        $newMember['rate'] = empty($students) ? '0%' : ($currentRank == 1 ? '100%' : round( (count($students) - $currentRank)/count($students) * 100) . "％");
        if(time() > $studentMember['lastRankChangeTime'] + 86400 ) {
            $newMember['lastRank'] = $studentMember['currentRank'] == 1 ? 1 : $studentMember['currentRank'];
            $newMember['lastRankChangeTime'] = time();
        }

        return $this->updateClassMember($newMember, $studentMember['id']); 
    }

    private function getClassesDao()
    {
        return $this->createDao('Classes.ClassesDao');
    }

    private function getClassMemberDao()
    {
        return $this->createDao('Classes.ClassMemberDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}