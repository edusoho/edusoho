<?php
namespace Topxia\Service\OpenCourse\Dao;

interface RecommendedCourseDao
{
    public function getRecommendedCourse($id);

    public function getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type);

    public function findRecommendedCoursesByOpenCourseId($oepnCourseId);

    public function addRecommendedCourse($recommended);

    public function deleteRecommendedCourse($id);

    public function updateRecommendedCourse($id, $fields);

    public function deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendCourseId);

    public function searchRecommendCount($conditions);

    public function searchRecommends($conditions, $orderBy, $start, $limit);

    public function findRandomRecommendCourses($courseId, $num);
}
