<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserApprovalDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserApprovalDaoImpl extends GeneralDaoImpl implements UserApprovalDao
{
    protected $table = 'user_approval';

    public function getLastestByUserIdAndStatus($userId, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND status = ? ORDER BY createdTime DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($userId, $status));
    }

    public function findByUserIds($userIds)
    {
        return $this->findInField('userId', $userIds);
    }

    protected function createQueryBuilder($conditions)
    {
        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && $conditions['keywordType'] == 'truename') {
            $conditions['truename'] = "%{$conditions['keyword']}%";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && $conditions['keywordType'] == 'idcard') {
            $conditions['idcard'] = "%{$conditions['keyword']}%";
        }

        return parent::createQueryBuilder($conditions);
    }

    public function declares()
    {
        return array(
            'orderbys' => array('id'),
            'conditions' => array(
                'truename LIKE :truename',
                'createTime >=:startTime',
                'createTime <=:endTime',
                'idcard LIKE :idcard',
            ),
        );
    }
}
