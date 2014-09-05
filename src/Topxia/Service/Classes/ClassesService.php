<?php

namespace Topxia\Service\Classes;

interface ClassesService
{
    /**
    *ClassService API
    */
    public function getClass($id);

    public function findClassesByIds(array $ids);

    public function searchClasses($conditions, $sort = 'latest', $start, $limit);

    public function searchClassCount($conditions);

    public function getStudentClass($userId);

    public function getClassHeadTeacher($classId);

    public function createClass($class);

    public function editClass($fields, $id);

    public function updateClassStudentNum($num,$id);

    public function deleteClass($id);

    public function checkPermission($name, $classId);

    public function getMemberByUserIdAndClassId($userId, $classId);

    public function findClassStudentMembers($classId);

    public function searchClassMembers(array $conditions, array $orderBy, $start, $limit);

    public function searchClassMemberCount(array $conditions);

    public function addClassMember(array $classMember);

    public function updateClassMember(array $fields, $id);

    public function deleteClassMemberByUserId($userId);

}