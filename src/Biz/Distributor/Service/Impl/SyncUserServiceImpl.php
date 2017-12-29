<?php

namespace Biz\Distributor\Service\Impl;

class SyncUserServiceImpl extends BaseSyncServiceImpl
{
    public function getNextJob()
    {
        return 'order';
    }

    protected function afterDeal()
    {
    }
}
