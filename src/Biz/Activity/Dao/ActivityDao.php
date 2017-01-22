<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityDao extends GeneralDaoInterface
{
    public function findByCourseId($courseId);

    public function findByIds($ids);
}
