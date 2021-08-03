<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassRecordService;

class MultiClassRecordServiceImpl extends BaseService implements MultiClassRecordService
{
    public function mackSign()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    /**
     * @return MultiClassRecordDao
     */
    protected function getMultiClassRecordDao()
    {
        return $this->createDao('MultiClass:MultiClassRecordDa');
    }
}
