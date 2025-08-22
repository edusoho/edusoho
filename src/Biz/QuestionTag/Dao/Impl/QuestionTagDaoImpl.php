<?php

namespace Biz\QuestionTag\Dao\Impl;

use Biz\QuestionTag\Dao\QuestionTagDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionTagDaoImpl extends AdvancedDaoImpl implements QuestionTagDao
{
    protected $table = 'question_tag';

    public function getByGroupIdAndName($groupId, $name)
    {
        return $this->getByFields(['groupId' => $groupId, 'name' => $name]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['seq'],
            'conditions' => [
                'groupId = :groupId',
                'name like :name',
                'status = :status',
            ],
        ];
    }
}
