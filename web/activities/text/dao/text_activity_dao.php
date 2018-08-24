<?php

namespace text\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class text_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_text';

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
