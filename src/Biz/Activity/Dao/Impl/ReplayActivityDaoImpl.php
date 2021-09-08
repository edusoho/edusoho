<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ReplayActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReplayActivityDaoImpl extends AdvancedDaoImpl implements ReplayActivityDao
{
    protected $table = 'activity_replay';

    public function declares()
    {
        // TODO: Implement declares() method.
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }
}
