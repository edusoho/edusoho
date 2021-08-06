<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassRecordService;
use Ramsey\Uuid\Uuid;

class MultiClassRecordServiceImpl extends BaseService implements MultiClassRecordService
{
    public function makeSign()
    {
        $sign = time() . '_' . Uuid::uuid4();
        $record = $this->getMultiClassRecordDao()->getRecordBySign($sign);
        if ($record){
            $sign = $this->makeSign();
        }

        return $sign;
    }

    /**
     * @return MultiClassRecordDao
     */
    protected function getMultiClassRecordDao()
    {
        return $this->createDao('MultiClass:MultiClassRecordDao');
    }
}
