<?php

namespace Biz\QuickEntrance\Service;

interface QuickEntranceService
{
    public function getEntrancesByUserId($userId);

    public function getAllEntrances($userId);

    public function createUserEntrance($userId, $entrances = array());

    public function updateUserEntrances($userId, $entrances = array());
}
