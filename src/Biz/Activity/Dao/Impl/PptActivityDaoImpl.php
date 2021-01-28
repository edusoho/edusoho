<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\PptActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PptActivityDaoImpl extends GeneralDaoImpl implements PptActivityDao
{
    protected $table = 'activity_ppt';

    public function declares()
    {
        return [
            'conditions' => [
                /*S2B2C 增加syncId*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
        ];
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }
}
