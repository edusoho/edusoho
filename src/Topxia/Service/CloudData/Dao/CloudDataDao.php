<?php

namespace Topxia\Service\CloudData\Dao;

interface CloudDataDao
{
    public function add($fields);

    public function deleteCloudData($id);

    public function getCloudData($id);

    public function searchCloudDataCount($conditions);

    public function searchCloudDatas($conditions, $orderBy, $start, $limit);
}
