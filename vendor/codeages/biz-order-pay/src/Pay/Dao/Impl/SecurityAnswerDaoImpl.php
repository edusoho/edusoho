<?php

namespace Codeages\Biz\Pay\Dao\Impl;

use Codeages\Biz\Pay\Dao\SecurityAnswerDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SecurityAnswerDaoImpl extends GeneralDaoImpl implements SecurityAnswerDao
{
    protected $table = 'biz_pay_security_answer';

    public function findByUserId($userId)
    {
        return $this->findByFields(array(
            'user_id' => $userId
        ));
    }

    public function getSecurityAnswerByUserIdAndQuestionKey($userId, $questionKey)
    {
        return $this->getByFields(array(
            'user_id' => $userId,
            'question_key' => $questionKey
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time')
        );
    }
}