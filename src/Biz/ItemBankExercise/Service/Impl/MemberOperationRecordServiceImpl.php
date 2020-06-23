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
        if (!ArrayToolkit::requireds($record, array('memberId', 'operateType'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getMemberOperationRecordDao()->create($record);
    }

    public function getJoinReasonByOrderId($orderId = 0)
    {
        $reason = [
            'reason' => 'site.join_by_free',
            'reasonType' => 'free_join',
        ];
        if (empty($orderId)) {
            return $reason;
        }

        $order = $this->getOrderService()->getOrder($orderId);
        if (empty($order)) {
            return $reason;
        }

        if ('outside' === $order['source']) {
            return array(
                'reason' => 'site.join_by_import',
                'reasonType' => 'import_join',
            );
        }

        if ($order['pay_amount'] > 0) {
            return array(
                'reason' => 'site.join_by_purchase',
                'reasonType' => 'buy_join',
            );
        }

        return $reason;
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
