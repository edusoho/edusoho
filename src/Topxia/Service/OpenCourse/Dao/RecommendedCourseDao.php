<?php
namespace Topxia\Service\OpenCourse\Dao;

interface RecommendedCourseDao
{
    public function getRecommendedCourse($id);

    public function findRecommendedCoursesByOpenCourseId($oepnCourseId);

    public function addRecommendedCourse($recommended);

    public function deleteRecommendedCourse($id);

    public function update($id, $fields);

    public function findRecommendCourse($openCourseId, $recommendCourseId);

    public function deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendCourseId);

    public function searchRecommendCount($conditions);

    public function searchRecommends($conditions, $orderBy, $start, $limit);

}
