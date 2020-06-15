<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ChapterExerciseRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ChapterExerciseRecordDaoImpl extends GeneralDaoImpl implements ChapterExerciseRecordDao
{
    protected $table = 'item_bank_chapter_exercise_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
