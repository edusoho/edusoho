<?php

namespace MarketingMallBundle\Biz\SyncList\Service;

interface SyncListService
{
    public function addSyncList($syncList);

    public function getSyncType();

    public function syncStatusUpdate($ids);

    public function getSyncIds();

    public function getSyncDataId($id);

    public function getSyncList($cursorAddress, $cursorType);
}

