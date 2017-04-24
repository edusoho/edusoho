<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\DocActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class DocActivityDaoImpl extends GeneralDaoImpl implements DocActivityDao
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
