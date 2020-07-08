<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseDaoImpl extends AdvancedDaoImpl implements ExerciseDao
{
    protected $table = 'item_bank_exercise';

    public function getByQuestionBankId($questionBankId)
    {
        return $this->getByFields(['questionBankId' => $questionBankId]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime', 'seq'],
            'serializes' => [
                'teacherIds' => 'delimiter',
                'cover' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'questionBankId = :questionBankId',
                'categoryId in (:categoryIds)',
                'creator = :creator',
                'title like :title',
                'status = :status',
            ],
        ];
    }
}
