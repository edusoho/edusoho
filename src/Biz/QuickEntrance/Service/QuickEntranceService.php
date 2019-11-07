<?php

namespace Biz\QuickEntrance\Service;

interface QuickEntranceService
{
    public function getEntrancesByUserId($userId);

    public function getAllEntrances($userId);

    public function updateUserEntrances($userId, $fields);
}
