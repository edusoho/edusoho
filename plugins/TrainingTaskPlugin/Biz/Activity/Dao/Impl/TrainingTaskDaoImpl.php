<?php

namespace TrainingTaskPlugin\Biz\Activity\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use TrainingTaskPlugin\Biz\Activity\Dao\TrainingTaskDao;

class TrainingTaskDaoImpl extends GeneralDaoImpl implements TrainingTaskDao
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

}