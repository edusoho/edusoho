<?php

namespace MarketingMallBundle\Biz\SyncList\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SyncListDao extends GeneralDaoInterface
{
    public function getSyncType();

    public function updateSyncType();

    public function getSyncListByCursor($cursorAddress, $cursorType);
}
