<?php

namespace MarketingMallBundle\Biz\SyncList\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SyncListDao extends GeneralDaoInterface
{
    public function getSyncType();

    public function syncStatusUpdate($ids);

    public function getSyncIds();

    public function getSyncDataId($id);

    public function getSyncListByCursor($cursorAddress, $cursorType);

    public function findSyncListByCursor($cursorAddress, $cursorType);
}
