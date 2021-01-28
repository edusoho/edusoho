<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ExerciseActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseActivityDaoImpl extends AdvancedDaoImpl implements ExerciseActivityDao
{
    protected $table = 'activity_exercise';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByAnswerSceneId($answerSceneId)
    {
        return $this->getByFields(['answerSceneId' => $answerSceneId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['drawCondition' => 'json'],
            'conditions' => [
                /*S2B2C增加syncId*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
        ];
    }
}
