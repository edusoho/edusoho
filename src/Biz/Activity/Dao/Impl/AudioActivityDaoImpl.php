<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\AudioActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AudioActivityDaoImpl extends GeneralDaoImpl implements AudioActivityDao
{
    protected $table = 'activity_audio';

    public function declares()
    {
        return [
            'serializes' => ['media' => 'json'],
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
