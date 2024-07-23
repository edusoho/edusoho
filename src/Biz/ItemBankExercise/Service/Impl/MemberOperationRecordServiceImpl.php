<?php

namespace Biz\ItemBankExercise\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\ItemBankExercise\Dao\MemberOperationRecordDao;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Codeages\Biz\Order\Service\OrderService;

class MemberOperationRecordServiceImpl extends BaseService implements MemberOperationRecordService
{
    public function count($conditions)
    {
        return $this->getMemberOperationRecordDao()->count($conditions);
    }

    public function search($conditions, $orderBy, $start, $limit)
    {
        return $this->getMemberOperationRecordDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function create($record)
    {
        if (!ArrayToolkit::requireds($record, ['memberId', 'operateType'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getMemberOperationRecordDao()->create($record);
    }

    public function updateRefundInfoByOrderId($orderId, $info)
    {
        $records = $this->getMemberOperationRecordDao()->findRecordsByOrderIdAndType($orderId, 'exit');
        foreach ($records as &$record)
        {
            $field = ArrayToolkit::parts($info, array('refundId', 'reason', 'reasonType'));
            if (!empty($record['reason'])) {
                unset($field['reason']);
                unset($field['reasonType']);
            }
            $record = array_merge($record, $field);
        }

        return $this->getMemberOperationRecordDao()->batchUpdate(ArrayToolkit::column($records, "id"), $records, 'id');
    }

    /**
     * @return MemberOperationRecordDao
     */
    protected function getMemberOperationRecordDao()
    {
        return $this->createDao('ItemBankExercise:MemberOperationRecordDao');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
