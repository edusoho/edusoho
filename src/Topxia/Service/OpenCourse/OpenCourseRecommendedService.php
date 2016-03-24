<?php
namespace Topxia\Service\OpenCourse;

interface OpenCourseRecommendedService
{
    public function addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds);

    public function findRecommendedCourseIdsByOpenCourseId($openCourseId);

    public function findRecommendCourse($classroomId, $courseId);
}
