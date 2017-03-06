<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserPayAgreementDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserPayAgreementDaoImpl extends GeneralDaoImpl implements UserPayAgreementDao
{
    protected $table = 'user_pay_agreement';

    public function getByUserIdAndBankAuth($userId, $bankAuth)
    {
        return $this->getByFields(array('userId' => $userId, 'bankAuth' => $bankAuth));
    }

    public function getByUserId($userId)
    {
        return $this->getByFields(array('userId' => $userId));
    }

    public function updateByUserIdAndBankAuth($userId, $bankAuth, $fields)
    {
        return $this->db()->update($this->table, $fields, array('userId' => $userId, 'bankAuth' => $bankAuth));
    }

    public function findByUserId($userId)
    {
        return $this->findInField('userId', array($userId));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'userId = :userId',
                'bankAuth = :bankAuth',
            ),
        );
    }
}
