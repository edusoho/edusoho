<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\TrainingActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TrainingActivityDaoImpl extends GeneralDaoImpl implements TrainingActivityDao
{
    protected $table = 'activity_training';

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
