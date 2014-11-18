<?php 
namespace Topxia\Service\Courseware;

interface CoursewareService
{
    public function getCourseware($id);

    public function searchCourseware(array $conditions, $sort, $start, $limit);

    public function searchCoursewareCount($conditions);

    public function createCourseware($courseware);

    public function updateCourseware($id,$courseware);

    public function deleteCourseware($id);

    public function deleteCoursewaresByIds($ids);
}