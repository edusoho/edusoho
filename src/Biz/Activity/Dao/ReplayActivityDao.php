<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ReplayActivityDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function findByLessonId($lessonId);
}
