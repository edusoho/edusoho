<?php

namespace Biz\Distributor\Service;

interface DistributorService
{
    public function findJobData();

    public function createJobData($data);

    public function getDrpService();
}
