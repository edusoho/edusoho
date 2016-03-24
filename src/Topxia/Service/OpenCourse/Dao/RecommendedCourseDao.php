<?php
namespace Topxia\Service\OpenCourse\Dao;

interface RecommendedCourseDao
{
    public function getRecommendedCourse($id);

    public function findRecommendedCourseIdsByOpenCourseId($oepnCourseId);

    public function addRecommendedCourse($recommended);

    public function deleteRecommendedCourse($id);

    public function update($id, $fields);

    public function findRecommendCourse($openCourseId, $recommendCourseId);

    public function deleteRecommendByOpenCouseIdAndRecommendCourseId($openCourseId, $recommendCourseId);

}
