<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\LiveActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LiveActivityDaoImpl extends GeneralDaoImpl implements LiveActivityDao
{
    protected $table = 'activity_live';

    public function declares()
    {
        return array(
            'conditions' => array(
                'id IN (:ids)',
                'liveProvider = :liveProvider',
                'replayStatus = :replayStatus',
                'progressStatus != :progressStatusNotEqual'
            )
        );
    }

    public function findByIds($Ids)
    {
        return $this->findInField('id', $Ids);
    }

    public function findByLiveIdAndReplayStatus($liveId)
    {
        return $this->findByFields(array('liveId' => $liveId, 'replayStatus' => 'ungenerated'));
    }
}
