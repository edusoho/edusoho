<?php

namespace Biz\Distributor\Service;

interface DistributorService
{
    public function findJobData();

    /**
     * $param $dataObj, user 或 order对象
     */
    public function createJobData($dataObj);

    public function batchUpdateStatus($jobData, $status);

    public function getDrpService();

    public function batchCreateJobData($jobData);
}
