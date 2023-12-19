<?php

namespace Biz\QuickEntrance\Service;

interface QuickEntranceService
{
    const QUICK_ENTRANCE_MAX_NUM = 8;

    public function findEntrancesByUserId($userId);

    public function findAvailableEntrances();

    public function findSelectedEntrancesCodeByUserId($userId);

    public function createUserEntrance($userId, $entrances = []);

    public function updateUserEntrances($userId, $entrances = []);
}
