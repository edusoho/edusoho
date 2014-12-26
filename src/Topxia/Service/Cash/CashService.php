<?php
namespace Topxia\Service\Cash;

interface CashService
{
    public function searchFlows($conditions, $orderBy, $start, $limit);

    public function searchFlowsCount($conditions);

    public function outflow($userId, $flow);

    public function inflow($userId, $flow);

    public function outFlowByCoin($outFlow);

    public function inFlowByRmb($inFlow);

    public function outFlowByRmb($outFlow);

    public function changeRmbToCoin($rmbFlow);
}