<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomDao
{
    public function searchClassrooms($conditions, $orderBy, $start, $limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);
 
}