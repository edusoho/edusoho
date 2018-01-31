<?php

namespace flash\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class flash_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_flash';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
        );
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}