<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface JobDao extends GeneralDaoInterface
{
    public function findWaitingJobsByLessThanFireTime($fireTime);

    public function getByName($name);
}
