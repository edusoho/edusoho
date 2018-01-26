<?php

namespace doc\dao;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class doc_activity_dao extends GeneralDaoImpl
{
    protected $table = 'activity_doc';

    public function declares()
    {
        return array('timestamps' => array('createdTime', 'updatedTime'));
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
