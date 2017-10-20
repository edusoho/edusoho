<?php

namespace Biz\MemberOperation\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
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
            throw $this->createInvalidArgumentException('参数不正确，记录创建失败！');
        }

        return $this->getRecordDao()->create($record);
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

    /**
     * @return MemberOperationRecordDao
     */
    protected function getRecordDao()
    {
        return $this->createDao('MemberOperation:MemberOperationRecordDao');
    }
}
