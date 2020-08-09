<?php

namespace Biz\OpenCourse\Service;

interface OpenCourseRecommendedService
{
    public function addRecommendedCourses($openCourseId, $recommendCourseIds, $origin);

    public function addRecommendGoods($openCourseId, $goodsIds);

    public function findRecommendedGoodsByOpenCourseId($openCourseId);

    public function updateOpenCourseRecommendedCourses($openCourseId, $activeCourseIds);

    /**
     * @before searchRecommendCount
     *
     * @param  $conditions
     *
     * @return mixed
     */
    public function countRecommends($conditions);

    public function searchRecommends($conditions, $orderBy, $start, $limit);

    public function recommendedGoodsSort($recommendCourses);

    public function getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type);

    public function findRandomRecommendGoods($courseId, $num = 3);

    public function deleteRecommend($recommendId);

    public function deleteBatchRecommend($recommendIds);
}
