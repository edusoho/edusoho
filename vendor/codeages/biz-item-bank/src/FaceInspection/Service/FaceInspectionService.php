<?php

namespace Codeages\Biz\ItemBank\FaceInspection\Service;

interface FaceInspectionService
{
    public function createRecord($record);

    public function countRecord($conditions);

    public function searchRecord($conditions, $orderBys, $start, $limit, $columns = array());

    public function makeToken($userId, $accessKey, $secretKey);
}
