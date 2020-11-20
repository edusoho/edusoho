<?php

namespace Biz\Visualization\Service;

interface DataCollectService
{
    public function push($data);

    public function createLearnFlow($userId, $activityId, $sign);

    public function getFlowBySign($userId, $sign);

    public function updateLearnFlow($id, $flow);
}
