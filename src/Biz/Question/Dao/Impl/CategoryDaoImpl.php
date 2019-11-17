<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\CategoryDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CategoryDaoImpl extends AdvancedDaoImpl implements CategoryDao
{
    protected $table = 'question_category';

    public function findByBankId($bankId)
    {
        return $this->findByFields(array('bankId' => $bankId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime', 'updateTime'),
            'conditions' => array(
                'id = :id',
            ),
        );
    }
}
