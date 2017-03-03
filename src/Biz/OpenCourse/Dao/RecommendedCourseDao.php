<?php

namespace Biz\OpenCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface RecommendedCourseDao extends GeneralDaoInterface
{
    public function getByCourseIdAndType($openCourseId, $recommendCourseId, $type);

    public function findByOpenCourseId($openCourseId);

    public function deleteByOpenCourseIdAndRecommendCourseId($openCourseId, $recommendCourseId);

    public function findRandomRecommendCourses($courseId, $num);
}
