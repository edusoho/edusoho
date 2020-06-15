<?php

namespace Biz\ItemBank\Dao\Impl;

use Biz\ItemBank\Dao\ExerciseModuleDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseModuleDaoImpl extends GeneralDaoImpl implements ExerciseModuleDao
{
    protected $table = 'item_bank_exercise_module';

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
