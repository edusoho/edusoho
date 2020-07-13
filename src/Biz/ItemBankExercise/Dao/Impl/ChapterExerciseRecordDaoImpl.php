<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ChapterExerciseRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ChapterExerciseRecordDaoImpl extends GeneralDaoImpl implements ChapterExerciseRecordDao
{
    protected $table = 'item_bank_chapter_exercise_record';

    public function getByAnswerRecordId($answerRecordId)
    {
        return $this->getByFields(['answerRecordId' => $answerRecordId]);
    }

    public function getLatestRecord($moduleId, $itemCategoryId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE moduleId = ? AND itemCategoryId = ? AND userId = ? ORDER BY id DESC;";

        return $this->db()->fetchAssoc($sql, [$moduleId, $itemCategoryId, $userId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id IN (:ids)',
                'itemCategoryId IN (:itemCategoryIds)',
                'userId = :userId',
                'moduleId = :moduleId',
            ],
        ];
    }
}
