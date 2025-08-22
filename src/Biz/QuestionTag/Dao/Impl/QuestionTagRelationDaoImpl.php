<?php

namespace Biz\QuestionTag\Dao\Impl;

use Biz\QuestionTag\Dao\QuestionTagRelationDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionTagRelationDaoImpl extends AdvancedDaoImpl implements QuestionTagRelationDao
{
    protected $table = 'question_tag_relation';

    public function findByTagIds($tagIds)
    {
        return $this->findInField('tagId', $tagIds);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['id'],
            'conditions' => [
                'tagId = :tagId',
                'tagId in (:tagIds)',
                'itemId = :itemId',
                'itemId in (:itemIds)',
            ],
        ];
    }
}
