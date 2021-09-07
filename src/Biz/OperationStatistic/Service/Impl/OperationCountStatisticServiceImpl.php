<?php


namespace Biz\OperationStatistic\Service\Impl;


use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\OperationStatistic\Dao\OperationCountStatisticDao;
use Biz\OperationStatistic\Service\OperationCountStatisticService;

class OperationCountStatisticServiceImpl extends BaseService implements OperationCountStatisticService
{
    public function createOperationRecord($operation)
    {
        $operation['version'] = \AppBundle\System::VERSION;
        $operation['operation_num'] = 1;
        $operation = ArrayToolkit::parts($operation, ['version', 'operator_id', 'target_type', 'operation_num']);

        return $this->getOperationCountStatisticDao()->create($operation);
    }

    public function getRecordByTargetTypeAndOperatorId($targetType, $operatorId)
    {
        return $this->getOperationCountStatisticDao()->getByTargetTypeAndOperatorId($targetType, $operatorId);
    }

    public function waveOperationNum($id)
    {
        return $this->getOperationCountStatisticDao()->wave([$id], ['operation_num' => 1]);
    }

    /**
     * @return OperationCountStatisticDao
     */
    protected function getOperationCountStatisticDao()
    {
        return $this->createDao('OperationStatistic:OperationCountStatisticDao');
    }
}