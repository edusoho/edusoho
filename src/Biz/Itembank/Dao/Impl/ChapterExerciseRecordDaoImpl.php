<?php

namespace Biz\ItemBank\Dao\Impl;

use Biz\ItemBank\Dao\ChapterExerciseRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ChapterExerciseRecordDaoImpl extends GeneralDaoImpl implements ChapterExerciseRecordDao
{
    protected $table = 'item_bank_chapter_exercise_record';

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
