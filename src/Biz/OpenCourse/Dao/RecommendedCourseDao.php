<?php
namespace Topxia\Service\OpenCourse\Dao;

interface RecommendedCourseDao
{
    public function getByCourseIdAndType($openCourseId, $recommendCourseId, $type);

    public function findByOpenCourseId($openCourseId);

    public function deleteByOpenCourseIdAndRecommendCourseId($openCourseId, $recommendCourseId);

    public function findRandom($courseId, $num);
}
