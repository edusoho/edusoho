<?php

namespace Biz\Sign\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Sign\Dao\SignTargetStatisticsDao;

class SignTargetStatisticsDaoImpl extends GeneralDaoImpl implements SignTargetStatisticsDao
{
    protected $table = 'sign_target_statistics';

    public function declares()
    {
    }

    public function getByTargetTypeAndTargetIdAndDate($targetType, $targetId, $date)
    {
        return $this->getByFields(array('targetType' => $targetType, 'targetId' => $targetId, 'date' => $date));
    }
}
