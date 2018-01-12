<?php

namespace Biz\MemberOperation\Service;

interface MemberOperationService
{
    public function getRecord($id);

    public function createRecord($record);

    public function countRecords($conditions);

    public function searchRecords($conditions, $orderBy, $start, $limit);

    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time');

    public function updateRefundInfoByOrderId($orderId, $info);

    public function getRecordByOrderIdAndType($orderId, $type);

    public function getJoinReasonByOrderId($orderId);

    public function countUserIdsByConditions($conditions);

    public function countGroupByUserId($field, $conditions);
}
