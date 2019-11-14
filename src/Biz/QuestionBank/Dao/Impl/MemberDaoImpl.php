<?php

namespace Biz\QuestionBank\Dao\Impl;

use Biz\QuestionBank\Dao\MemberDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MemberDaoImpl extends AdvancedDaoImpl implements MemberDao
{
    protected $table = 'question_bank_member';

    public function findByBankId($bankId)
    {
        return $this->findByFields(array('bankId' => $bankId));
    }

    public function declares()
    {
        $declares['timestamps'] = array(
            'createdTime',
        );

        $declares['orderbys'] = array(
            'id',
        );

        $declares['conditions'] = array(
            'id = :id',
            'bankId = :bankId'
        );

        return $declares;
    }
}
