<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\QuestionDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class QuestionDaoImpl extends GeneralDaoImpl implements QuestionDao
{
    protected $table = 'question';

    public function findQuestionsByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findQuestionsByParentId($id)
    {
        return $this->findInField('parentId', array($id));
    }

    public function findQuestionsByCourseSetId($courseSetId)
    {
        return $this->findInField('courseSetId', array($courseSetId));
    }

    public function deleteSubQuestions($parentId)
    {
        return $this->db()->delete($this->table(), array('parentId' => $parentId));
    }

    public function getQuestionCountGroupByTypes($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('count(id) as questionNum, type');

        $builder->addGroupBy('type');

        return $builder->execute()->fetchAll() ?: array();
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime',
            'updatedTime',
        );

        $declares['conditions'] = array(
            'id IN ( :ids )',
            'parentId = :parentId',
            'difficulty = :difficulty',
            'type = :type',
            'type IN ( :types )',
            'stem LIKE :stem',
            'subCount <> :subCount',
            'id NOT IN ( :excludeIds )',
            'courseId = :courseId',
            'courseId IN (:courseIds)',
            'courseSetId = :courseSetId',
            'courseSetId IN (:courseSetIds)',
            'lessonId = :lessonId',
            'lessonId >= :lessonIdGT',
            'lessonId <= :lessonIdLT',
            'lessonId IN ( :lessonIds)',
            'copyId = :copyId',
            'copyId IN (:copyIds)',
        );

        $declares['serializes'] = array(
            'answer' => 'json',
            'metas' => 'json',
        );

        return $declares;
    }
}
