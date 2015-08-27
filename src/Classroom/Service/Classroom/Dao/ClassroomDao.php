<?php

namespace Classroom\Service\Classroom\Dao;

interface ClassroomDao
{
    public function getClassroom($id);

    public function updateClassroom($id, $fields);

    public function waveClassroom($id, $field, $diff);

    public function searchClassrooms($conditions, $orderBy, $start, $limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);

    public function findClassroomByTitle($title);

    public function findClassroomsByLikeTitle($title);

    public function deleteClassroom($id);

    public function findClassroomsByIds(array $ids);
}
