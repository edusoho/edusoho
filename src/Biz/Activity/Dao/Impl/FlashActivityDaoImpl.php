<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\FlashActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FlashActivityDaoImpl extends GeneralDaoImpl implements FlashActivityDao
{
    protected $table = 'activity_flash';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
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
