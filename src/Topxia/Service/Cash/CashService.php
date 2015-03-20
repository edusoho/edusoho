<?php
namespace Topxia\Service\Cash;

interface CashService
{
    public function searchFlows($conditions, $orderBy, $start, $limit);

    public function searchFlowsCount($conditions);

    public function outflowByCoin($outflow);

    public function inflowByRmb($inflow);

    public function outflowByRmb($outflow);

    public function changeRmbToCoin($rmbFlow);

    public function inflowByCoin($inflow);

    public function analysisAmount($conditions);

    public function findUserIdsByFlows($type,$createdTime,$orderBy, $start, $limit);

    public function findUserIdsByFlowsCount($type,$createdTime);
}