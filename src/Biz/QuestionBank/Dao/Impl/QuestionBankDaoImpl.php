<?php

namespace Biz\QuestionBank\Dao\Impl;

use Biz\QuestionBank\Dao\QuestionBankDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionBankDaoImpl extends AdvancedDaoImpl implements QuestionBankDao
{
    protected $table = 'question_bank';

    public function getByCourseSetId($courseSetId)
    {
        return $this->getByFields(['fromCourseSetId' => $courseSetId]);
    }

    public function getByItemBankId($itemBankId)
    {
        return $this->getByFields(['itemBankId' => $itemBankId]);
    }

    public function findByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE `isHidden` = 0 and `id` IN ({$marks});";

        return $this->db()->fetchAll($sql, $ids) ?: [];
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
        ];

        $declares['conditions'] = [
            'id = :id',
            'categoryId = :categoryId',
            'orgCode like :likeOrgCode',
            'categoryId IN (:categoryIds)',
            'id IN (:ids)',
            'isHidden = :isHidden',
            'name like :nameLike',
        ];

        return $declares;
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()} where `isHidden` = 0 ORDER BY `id` ASC";

        return $this->db()->fetchAll($sql) ?: [];
    }
}
