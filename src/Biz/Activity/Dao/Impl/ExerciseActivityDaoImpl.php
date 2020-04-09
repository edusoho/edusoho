<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ExerciseActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseActivityDaoImpl extends GeneralDaoImpl implements ExerciseActivityDao
{
    protected $table = 'activity_exercise';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('drawCondition' => 'json'),
        );
    }
}
