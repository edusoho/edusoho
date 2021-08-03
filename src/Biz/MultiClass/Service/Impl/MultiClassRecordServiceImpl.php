<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassRecordService;

class MultiClassRecordServiceImpl extends BaseService implements MultiClassRecordService
{

    /**
     * @return MultiClassRecordDao
     */
    protected function getMultiClassRecordDao()
    {
        return $this->createDao('MultiClass:MultiClassRecordDa');
    }
}
