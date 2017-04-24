<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\TextActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TextActivityDaoImpl extends GeneralDaoImpl implements TextActivityDao
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
