<?php
namespace Topxia\Service\Cash;

interface CashService
{
    public function searchFlows($conditions, $orderBy, $start, $limit);

    public function searchFlowsCount($conditions);

    public function outFlowByCoin($outFlow);

    public function inFlowByRmb($inFlow);

    public function outFlowByRmb($outFlow);

    public function changeRmbToCoin($rmbFlow);

    public function inflowByCoin($inflow);

    public function analysisAmount($conditions);

    public function findUserIdsByFlows($type,$createdTime,$orderBy, $start, $limit);

    public function findUserIdsByFlowsCount($type,$createdTime);
}