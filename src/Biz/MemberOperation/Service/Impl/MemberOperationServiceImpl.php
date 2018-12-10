<?php

namespace Biz\MemberOperation\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\MemberOperation\Dao\MemberOperationRecordDao;
use Biz\MemberOperation\Service\MemberOperationService;

class MemberOperationServiceImpl extends BaseService implements MemberOperationService
{
    public function getRecord($id)
    {
        return $this->getRecordDao()->get($id);
    }

    public function createRecord($record)
    {
        if (!ArrayToolkit::requireds($record, array('member_id', 'target_type', 'operate_type'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getRecordDao()->create($record);
    }

    public function countGroupByUserId($field, $conditions)
    {
        $result = $this->getRecordDao()->countGroupByUserId($field, $conditions);

        return ArrayToolkit::index($result, 'user_id');
    }

    public function updateRefundInfoByOrderId($orderId, $info)
    {
        $record = $this->getRecordByOrderIdAndType($orderId, 'exit');

        $field = ArrayToolkit::parts($info, array('refund_id', 'reason', 'reason_type'));
        if (!empty($record['reason'])) {
            unset($field['reason']);
            unset($field['reason_type']);
        }

        return $this->getRecordDao()->update($record['id'], $field);
    }

    public function getJoinReasonByOrderId($orderId = 0)
    {
        $reason = array(
                'reason' => 'site.join_by_free',
                'reason_type' => 'free_join',
        );
        if (empty($orderId)) {
            return $reason;
        }

        $order = $this->getOrderService()->getOrder($orderId);
        if (empty($order)) {
            return $reason;
        }

        if ('markting' === $order['source']) {
            return array(
                'reason' => 'site.join_by_markting',
                'reason_type' => 'markting_join',
            );
        }

        if ('outside' === $order['source']) {
            return array(
                'reason' => 'site.join_by_import',
                'reason_type' => 'import_join',
            );
        }

        if ($order['pay_amount'] > 0) {
            return array(
                'reason' => 'site.join_by_purchase',
                'reason_type' => 'buy_join',
            );
        }

        return $reason;
    }

    public function countRecords($conditions)
    {
        return $this->getRecordDao()->count($conditions);
    }

    public function searchRecords($conditions, $orderBy, $start, $limit)
    {
        return $this->getRecordDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'operate_time')
    {
        return $this->getRecordDao()->countGroupByDate($conditions, $sort, $dateColumn);
    }

    public function getRecordByOrderIdAndType($orderId, $type)
    {
        return $this->getRecordDao()->getRecordByOrderIdAndType($orderId, $type);
    }

    public function countUserIdsByConditions($conditions)
    {
        return $this->getRecordDao()->countUserIdsByConditions($conditions);
    }

    /**
     * @return MemberOperationRecordDao
     */
    protected function getRecordDao()
    {
        return $this->createDao('MemberOperation:MemberOperationRecordDao');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
