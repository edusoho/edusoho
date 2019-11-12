<?php

namespace Biz\QuestionBank\Dao\Impl;

use Biz\QuestionBank\Dao\CategoryDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CategoryDaoImpl extends AdvancedDaoImpl implements CategoryDao
{
    protected $table = 'question_bank_category';

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table()}";

        return $this->db()->fetchAll($sql) ?: array();
    }

    public function declares()
    {
        $declares['timestamps'] = array(
            'createdTime',
            'updatedTime',
        );

        $declares['orderbys'] = array(
            'id',
        );

        $declares['conditions'] = array(
            'id = :id',
        );

        return $declares;
    }
}
