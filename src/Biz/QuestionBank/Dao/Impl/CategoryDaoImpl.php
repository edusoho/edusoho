<?php

namespace Biz\QuestionBank\Dao\Impl;

use Biz\QuestionBank\Dao\CategoryDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CategoryDaoImpl extends AdvancedDaoImpl implements CategoryDao
{
    protected $table = 'question_bank_category';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findAllByParentId($parentId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE parentId = ?";

        return $this->db()->fetchAll($sql, [$parentId]) ?: [];
    }

    public function findByPrefixOrgCode($orgCode)
    {
        if (empty($orgCode)) {
            return [];
        }

        $sql = "SELECT * FROM {$this->table()} WHERE `orgCode` LIKE ?";

        return $this->db()->fetchAll($sql, [$orgCode.'%']) ?: [];
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()} ORDER BY weight ASC";

        return $this->db()->fetchAll($sql) ?: [];
    }

    public function declares()
    {
        $declares['timestamps'] = [
            'createdTime',
            'updatedTime',
        ];

        $declares['orderbys'] = [
            'id',
        ];

        $declares['conditions'] = [
            'id = :id',
            'id in (:ids)',
        ];

        return $declares;
    }
}
