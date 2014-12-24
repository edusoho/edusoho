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
}