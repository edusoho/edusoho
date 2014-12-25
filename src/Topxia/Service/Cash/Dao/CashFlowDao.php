<?php

namespace Topxia\Service\Cash\Dao;

interface CashFlowDao
{

    public function getFlow($id);

    public function getFlowBySn($sn);

    public function getFlowByOrderSn($orderSn);

    public function searchFlows($conditions, $orderBy, $start, $limit);

    public function searchFlowsCount($conditions);

    public function addFlow($flow);

    public function updateFlow($flow);

    public function analysisAmount($conditions);

    public function findUserIdsByFlows($type,$createdTime, $orderBy, $start, $limit);

    public function findUserIdsByFlowsCount($type,$createdTime);
}