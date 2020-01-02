<?php

namespace Biz\QuestionBank\Dao\Impl;

use Biz\QuestionBank\Dao\QuestionBankDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionBankDaoImpl extends AdvancedDaoImpl implements QuestionBankDao
{
    protected $table = 'question_bank';

    public function getByCourseSetId($courseSetId)
    {
        return $this->getByFields(array('fromCourseSetId' => $courseSetId));
    }

    public function findByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE `isHidden` = 0 and `id` IN ({$marks});";

        return $this->db()->fetchAll($sql, $ids) ?: array();
    }

    public function declares()
    {
        $declares['timestamps'] = array(
            'createdTime',
            'updatedTime',
        );

        $declares['orderbys'] = array(
            'id',
            'createdTime',
        );

        $declares['conditions'] = array(
            'id = :id',
            'categoryId = :categoryId',
            'orgCode like :likeOrgCode',
            'categoryId IN (:categoryIds)',
            'id IN (:ids)',
            'isHidden = :isHidden',
            'name like :nameLike',
        );

        return $declares;
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()} where `isHidden` = 0 ORDER BY `id` ASC";

        return $this->db()->fetchAll($sql) ?: array();
    }
}
