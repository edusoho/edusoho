<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomDao
{   
    public function getClassroom($id);

    public function updateClassroom($id,$fields);
    
    public function searchClassrooms($conditions, $orderBy, $start, $limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);

    public function deleteClassroom($id);
 
}