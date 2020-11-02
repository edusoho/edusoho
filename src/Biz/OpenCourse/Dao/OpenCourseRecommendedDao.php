<?php

namespace Biz\OpenCourse\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface OpenCourseRecommendedDao extends AdvancedDaoInterface
{
    public function getByOpenCourseIdAndGoodsId($openCourseId, $goodsId);

    public function getByCourseIdAndType($openCourseId, $recommendCourseId, $type);

    public function findByOpenCourseId($openCourseId);

    public function deleteByOpenCourseIdAndRecommendCourseId($openCourseId, $recommendCourseId);

    public function findRandomRecommendCourses($courseId, $num);
}
