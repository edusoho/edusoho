<?php

namespace Biz\QuickEntrance\Service;

interface QuickEntranceService
{
    const QUICK_ENTRANCE_MAX_NUM = 7;

    public function getEntrancesByUserId($userId);

    public function getAllEntrances($userId);

    public function findAvailableEntrancesByUserId($userId);

    public function findSelectedEntrancesByUserId();

    public function createUserEntrance($userId, $entrances = array());

    public function updateUserEntrances($userId, $entrances = array());
}
