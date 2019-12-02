<?php

namespace Biz\QuickEntrance\Service;

interface QuickEntranceService
{
    const QUICK_ENTRANCE_MAX_NUM = 7;

    public function findEntrancesByUserId($userId);

    public function findAvailableEntrances();

    public function findSelectedEntrancesCodeByUserId($userId);

    public function createUserEntrance($userId, $entrances = array());

    public function updateUserEntrances($userId, $entrances = array());
}
