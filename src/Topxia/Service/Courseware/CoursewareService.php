<?php 
namespace Topxia\Service\Courseware;

interface CoursewareService
{
    public function getCourseware($id);

    public function searchCoursewares(array $conditions, $orderBy, $start, $limit);

    public function searchCoursewaresCount($conditions);

    public function createCourseware($courseware);

    public function updateCourseware($id,$courseware);

    public function deleteCourseware($id);
}