<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ReviewDao extends GeneralDaoInterface
{
    public function getReviewByUserIdAndCourseId($userId, $courseId);

    public function sumRatingByParams($conditions);

    public function deleteByCourseId($courseId);
}
