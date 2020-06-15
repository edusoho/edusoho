<?php

namespace Biz\ItemBank\Dao\Impl;

use Biz\ItemBank\Dao\ExerciseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseDaoImpl extends GeneralDaoImpl implements ExerciseDao
{
    protected $table = 'item_bank_exercise';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'id = :id',
            ),
        );
    }
}
