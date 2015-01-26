<?php

namespace Topxia\Service\Classroom;

interface ClassroomService

{   
    public function getClassromm($id);
    
    public function searchClassrooms($conditions,$orderBy,$start,$limit);

    public function searchClassroomsCount($condtions);

}