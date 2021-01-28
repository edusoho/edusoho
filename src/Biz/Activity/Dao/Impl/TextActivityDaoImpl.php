<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\TextActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TextActivityDaoImpl extends GeneralDaoImpl implements TextActivityDao
{
    protected $table = 'activity_text';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                /*S2B2C增加syncId*/
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
