<?php

namespace Topxia\Service\Courseware\Dao;

interface CoursewareDao
{
    public function getCourseware($id);

    public function searchCoursewares($conditions, $orderBys, $start, $limit);

    public function searchCoursewaresCount($conditions);
    
    public function addCourseware($courseware);

    public function updateCourseware($id,$courseware);

    public function deleteCourseware($id);
}