<?php

namespace live\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class live_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_live';

    public function declares()
    {
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}