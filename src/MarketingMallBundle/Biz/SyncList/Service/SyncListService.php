<?php

namespace MarketingMallBundle\Biz\SyncList\Service;

interface SyncListService
{
    public function addSyncList($syncList);

    public function getSyncType();

    public function updateSyncType();

    public function getSyncList($cursorAddress, $cursorType);
}

