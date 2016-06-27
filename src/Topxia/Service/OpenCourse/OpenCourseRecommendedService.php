<?php
namespace Topxia\Service\OpenCourse;

interface OpenCourseRecommendedService
{
    public function addRecommendedCourses($openCourseId, $recommendCourseIds, $origin);

    public function findRecommendedCoursesByOpenCourseId($openCourseId);

    public function updateOpenCourseRecommendedCourses($openCourseId, $activeCourseIds);

    public function searchRecommendCount($conditions);

    public function searchRecommends($conditions, $orderBy, $start, $limit);

    public function recommendedCoursesSort($recommendCourses);

    public function getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type);

    public function findRandomRecommendCourses($courseId, $num=3);
}
