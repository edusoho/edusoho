<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserSecureQuestionDao;
use Topxia\Common\DaoException;
use PDO;

class UserSecureQuestionDaoImpl extends BaseDao implements UserSecureQuestionDao
{
    protected $table = 'user_secure_question';

    public function getUserSecureQuestionsByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime ASC ";
        return  $this->getConnection()->fetchAll($sql, array($userId)) ? : null; 
    }

    public function addOneUserSecureQuestion($filedsWithUserIdAndQuestionNumAndQuestionAndHashedAnswerAndAnswerSalt)
    {
        $affected = $this->getConnection()->insert($this->table, $filedsWithUserIdAndQuestionNumAndQuestionAndHashedAnswerAndAnswerSalt);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert user_secure_question error.');
        }
        return true;      
    }

}