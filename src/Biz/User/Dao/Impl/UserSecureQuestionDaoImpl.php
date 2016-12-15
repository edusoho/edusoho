<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserSecureQuestionDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserSecureQuestionDaoImpl extends GeneralDaoImpl implements UserSecureQuestionDao
{
    protected $table = 'user_secure_question';

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime ASC ";
        return $this->db()->fetchAll($sql, array($userId)) ?: null;
    }

    public function declares()
    {
        return array(
        );
    }
}
