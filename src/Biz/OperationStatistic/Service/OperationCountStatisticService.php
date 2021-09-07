<?php

namespace Biz\OperationStatistic\Service;

interface OperationCountStatisticService
{
    public function createOperationRecord($operation);

    public function getRecordByTargetTypeAndOperatorId($targetType, $operatorId);

    public function waveOperationNum($id);
}
