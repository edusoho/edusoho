<?php

namespace Biz\QuestionTag\Dao\Impl;

use Biz\QuestionTag\Dao\QuestionTagGroupDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionTagGroupDaoImpl extends AdvancedDaoImpl implements QuestionTagGroupDao
{
    protected $table = 'question_tag_group';

    public function getByName($name)
    {
        return $this->getByFields(['name' => $name]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['seq'],
            'conditions' => [
                'name like :name',
                'status = :status',
            ],
        ];
    }
}
