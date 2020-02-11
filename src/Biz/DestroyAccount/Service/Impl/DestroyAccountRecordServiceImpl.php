<?php

namespace Biz\DestroyAccount\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\DestroyAccount\Dao\DestroyAccountRecordDao;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;

class DestroyAccountRecordServiceImpl extends BaseService implements DestroyAccountRecordService
{
    public function getDestroyAccountRecord($id)
    {
        return $this->getDestroyAccountRecordDao()->get($id);
    }

    public function updateDestroyAccountRecord($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('status'));

        return $this->getDestroyAccountRecordDao()->update($id, $fields);
    }

    public function createDestroyAccountRecord($fields)
    {
        return $this->getDestroyAccountRecordDao()->create($fields);
    }

    public function deleteDestroyAccountRecord($id)
    {
        return $this->getDestroyAccountRecordDao()->delete($id);
    }

    public function getLastDestroyAccountRecordByUserId($userId)
    {
        return $this->getDestroyAccountRecordDao()->getLastDestroyAccountRecordByUserId($userId);
    }

    public function searchDestroyAccountRecords($conditions, $orderBy, $start, $limit)
    {
        $records = $this->getDestroyAccountRecordDao()->search($conditions, $orderBy, $start, $limit);

        return $records;
    }

    public function countDestroyAccountRecords($conditions)
    {
        return $this->getDestroyAccountRecordDao()->count($conditions);
    }

    /**
     * @return DestroyAccountRecordDao
     */
    protected function getDestroyAccountRecordDao()
    {
        return $this->createDao('DestroyAccount:DestroyAccountRecordDao');
    }
}
