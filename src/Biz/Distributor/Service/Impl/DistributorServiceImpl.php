<?php

namespace Biz\Distributor\Service\Impl;

use Biz\BaseService;

class DistributorServiceImpl extends BaseService implements DistributorService
{
    public function validateToken($token)
    {
    }

    protected function getDistributorService()
    {
        return $this->createService('Distributor:DistributorService');
    }
}
