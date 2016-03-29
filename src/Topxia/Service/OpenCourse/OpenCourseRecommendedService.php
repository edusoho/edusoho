<?php
namespace Topxia\Service\OpenCourse;

interface OpenCourseRecommendedService
{
    public function addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds);

    public function findRecommendedCoursesByOpenCourseId($openCourseId);

    public function findRecommendCourse($classroomId, $courseId);
}
