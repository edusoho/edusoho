<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserSecureQuestionDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserSecureQuestionDaoImpl extends GeneralDaoImpl implements UserSecureQuestionDao
{
    protected $table = 'user_secure_question';

    public function findByUserId($userId)
    {
        return $this->findByFields(array(
            'userId' => $userId,
        ));
    }

    public function declares()
    {
        return array(
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'userId = :userId',
            ),
        );
    }
}
