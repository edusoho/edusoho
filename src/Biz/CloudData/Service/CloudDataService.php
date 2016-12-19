<?php

namespace Biz\CloudData\Service;

interface CloudDataService
{
    public function push($name, array $body = array(), $timestamp = 0, $level = 'normal');

    public function searchCloudDataCount($conditions);

    public function searchCloudDatas($conditions, $orderBy, $start, $limit);

    public function deleteCloudData($id);
}
