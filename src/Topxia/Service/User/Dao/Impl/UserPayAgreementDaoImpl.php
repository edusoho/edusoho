<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserPayAgreementDao;

class UserPayAgreementDaoImpl extends BaseDao implements UserPayAgreementDao
{
    protected $table = 'user_pay_agreement';

    public function getUserPayAgreement($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth)
    {
        $sql = "SELECT * FROM {$this->table} WHERE  userId =? and bankAuth = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $bankAuth)) ?: null;
    }

    public function getUserPayAgreementByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ?: null;
    }

    public function addUserPayAgreement($field)
    {
        $this->getConnection()->insert($this->table, $field);
        return $this->getUserPayAgreement($this->getConnection()->lastInsertId());
    }

    public function updateUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth, $fields)
    {
        return $this->getConnection()->update($this->table, $fields, array('userId' => $userId, 'bankAuth' => $bankAuth));
    }

    public function findUserPayAgreementsByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ";
        return $this->getConnection()->fetchAll($sql, array($userId)) ?: array();
    }

    public function deleteUserPayAgreements($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}
