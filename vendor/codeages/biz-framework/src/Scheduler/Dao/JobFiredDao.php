<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface JobFiredDao extends GeneralDaoInterface
{
    public function getByStatus($status);

    public function findByJobId($jobId);

    public function findByJobIdAndStatus($jobId, $status);

    public function deleteUnacquiredBeforeCreatedTime($time);
}
