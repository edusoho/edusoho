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

    public function search($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createProfileQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function count($conditions)
    {
        $builder = $this->createProfileQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createProfileQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($v) {
            if ($v === 0) {
                return true;
            }

            if (empty($v)) {
                return false;
            }
            return true;
        });

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && $conditions['keywordType'] == 'truename') {
            $conditions['truename'] = "%{$conditions['keyword']}%";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword']) && $conditions['keywordType'] == 'idcard') {
            $conditions['idcard'] = "%{$conditions['keyword']}%";
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user_approval')
            ->andWhere('truename LIKE :truename')
            ->andWhere('createTime >=:startTime')
            ->andWhere('createTime <=:endTime')
            ->andWhere('idcard LIKE :idcard');
    }

    public function declares()
    {
        return array(
        );
    }
}
