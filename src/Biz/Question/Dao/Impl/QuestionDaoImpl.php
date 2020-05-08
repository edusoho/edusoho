<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\QuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionDaoImpl extends AdvancedDaoImpl implements QuestionDao
{
    protected $table = 'question';

    public function findQuestionsByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findQuestionsByParentId($id)
    {
        return $this->findInField('parentId', [$id]);
    }

    public function findQuestionsByCourseSetId($courseSetId)
    {
        return $this->findInField('courseSetId', [$courseSetId]);
    }

    public function findQuestionsByCopyId($copyId)
    {
        return $this->findInField('copyId', [$copyId]);
    }

    public function findQuestionsByCategoryIds($categoryIds)
    {
        return $this->findInField('categoryId', $categoryIds);
    }

    public function deleteSubQuestions($parentId)
    {
        return $this->db()->delete($this->table(), ['parentId' => $parentId]);
    }

    public function deleteByCourseSetId($courseSetId)
    {
        return $this->db()->delete($this->table(), ['courseSetId' => $courseSetId]);
    }

    public function copyQuestionsUpdateSubCount($parentId, $subCount)
    {
        $sql = "UPDATE {$this->table()} SET subCount = ? WHERE copyId = ?";

        return $this->db()->executeUpdate($sql, [$subCount, $parentId]);
    }

    public function getQuestionCountGroupByTypes($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('count(id) as questionNum, type');

        $builder->addGroupBy('type');

        return $builder->execute()->fetchAll() ?: [];
    }

    public function findBySyncIds(array $syncIds)
    {
        return $this->findInField('syncId', $syncIds);
    }

    public function declares()
    {
        $declares['timestamps'] = [
            'createdTime',
            'updatedTime',
        ];

        $declares['orderbys'] = [
            'id',
            'createdTime',
            'updatedTime',
        ];

        $declares['conditions'] = [
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
            'parentId > :parentIdGT',
            'categoryId IN (:categoryIds)',
            'bankId = :bankId',
            'categoryId = :categoryId',
        ];

        $declares['serializes'] = [
            'answer' => 'json',
            'metas' => 'json',
        ];

        return $declares;
    }
}
