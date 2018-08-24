<?php

namespace ppt\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ppt_activity_dao extends GeneralDaoImpl
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