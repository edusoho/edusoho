<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\PptActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PptActivityDaoImpl extends GeneralDaoImpl implements PptActivityDao
{
    protected $table = 'activity_ppt';

    public function declares()
    {
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
