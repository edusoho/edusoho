<?php

namespace Topxia\Service\Classroom\Dao;

interface ClassroomDao
{   
<<<<<<< HEAD
=======
    public function getClassroom($id);
>>>>>>> origin/feature/classroom
    
    public function searchClassrooms($conditions, $orderBy, $start, $limit);

    public function searchClassroomsCount($condtions);

    public function addClassroom($classroom);
 
}