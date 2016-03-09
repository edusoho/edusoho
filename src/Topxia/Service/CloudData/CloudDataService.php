<?php

namespace Topxia\Service\CloudData;

interface CloudDataService
{
    public function push($name, array $body = array(), $timestamp, $tryTimes = 0);

    public function searchCloudDataCount($conditions);

    public function searchCloudDatas($conditions, $orderBy, $start, $limit);
}
