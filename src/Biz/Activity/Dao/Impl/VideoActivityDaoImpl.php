<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\VideoActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class VideoActivityDaoImpl extends GeneralDaoImpl implements VideoActivityDao
{
    protected $table = 'activity_video';

    public function declares()
    {
        return [
            'serializes' => ['media' => 'json'],
            'conditions' => [
                /*S2B2C新增syncId*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
        ];
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }
}
