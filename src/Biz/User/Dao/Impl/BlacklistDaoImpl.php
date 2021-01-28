<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\BlacklistDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class BlacklistDaoImpl extends GeneralDaoImpl implements BlacklistDao
{
    protected $table = 'blacklist';

    public function getByUserIdAndBlackId($userId, $blackId)
    {
        return $this->getByFields(array(
            'userId' => $userId,
            'blackId' => $blackId,
        ));
    }

    public function findByUserId($userId)
    {
        return $this->findInField('userId', array($userId));
    }

    public function deleteByUserIdAndBlackId($userId, $blackId)
    {
        return $this->db()->delete($this->table, array('userId' => $userId, 'blackId' => $blackId));
    }

    public function declares()
    {
        return array(
        );
    }
}
