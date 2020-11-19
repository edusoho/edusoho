<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface CoursePlanLearnDailyDao extends AdvancedDaoInterface
{
    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);

    public function sumPureLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds);

    public function sumLearnedTimeByCourseId($courseId);
}
