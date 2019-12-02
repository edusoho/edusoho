<?php

namespace Biz\QuestionBank\Dao\Impl;

use Biz\QuestionBank\Dao\MemberDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MemberDaoImpl extends AdvancedDaoImpl implements MemberDao
{
    protected $table = 'question_bank_member';

    public function getByBankIdAndUserId($bankId, $userId)
    {
        return $this->getByFields(array('bankId' => $bankId, 'userId' => $userId));
    }

    public function findByBankId($bankId)
    {
        return $this->findByFields(array('bankId' => $bankId));
    }

    public function findByUserId($userId)
    {
        return $this->findByFields(array('userId' => $userId));
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
            'bankId = :bankId',
            'userId = :userId',
        );

        return $declares;
    }
}
