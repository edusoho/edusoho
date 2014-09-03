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
}