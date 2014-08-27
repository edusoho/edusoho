<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Classes\ClassMemberService;
use Topxia\Service\Common\BaseService;

class ClassMemberServiceImpl extends BaseService implements ClassMemberService
{
    public function getClassMemberByUserId($userId){
        return $this->getClassMemberDao()->getClassMemberByUserId($userId);
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


    private function getClassMemberDao()
    {
        return $this->createDao('Classes.ClassMemberDao');
    }
}