<?php
namespace Topxia\Service\Classes\Impl;

use Topxia\Service\Classes\ClassesService;
use Topxia\Service\Common\BaseService;

class ClassesServiceImpl extends BaseService implements ClassesService
{
    public function getClass($id)
    {
        return $this->getClassesDao()->getClass($id);
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

    public function editClass($fields, $id)
    {
        $class = $this->getClassesDao()->editClass($fields, $id);
      /*  $conditions = array(
            'classId' => $class['id'],
            'role' => 'HEAD_TEACHER'
            );
        $oldClassMember = $this->getClassMemberService()->searchClassMembers(
            $conditions,
            array('id','DESC'),
            0,
            1);
        if($oldClassMember['userId'] != $class['headTeacherId']) {
            $this->getClassMemberService()->addClassMember($classMember);
        }*/

        
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

    private function getClassMemberService(){
        return $this->createService('Classes.ClassMemberService');
    }
}