<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserDao;

class UserDaoImpl extends BaseDao implements UserDao
{
    protected $table = 'user';

    public function getUser($id, $lock = false)
    {
        if ($lock) {
            $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

            if ($lock) {
                $sql .= " FOR UPDATE";
            }

            return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        $that = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function findUserByEmail($email)
    {
        $that = $this;

        return $this->fetchCached("email:{$email}", $email, function ($email) use ($that) {
            $sql    = "SELECT * FROM {$that->getTable()} WHERE email = ? LIMIT 1";
            $result = $that->getConnection()->fetchAssoc($sql, array($email));
            return $result?:array();
        });
    }

    public function findUserByNickname($nickname)
    {
        if(empty($nickname)) {
            return array();
        }

        $that = $this;

        return $this->fetchCached("nickname:{$nickname}", $nickname, function ($nickname) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE nickname = ? LIMIT 1";
            $result = $that->getConnection()->fetchAssoc($sql, array($nickname));
            return $result ? : array();
        });
    }

    public function getCountByMobileNotEmpty()
    {
        $sql = "SELECT COUNT(DISTINCT `mobile`) FROM `user` AS u, `user_profile` AS up WHERE u.id = up.id AND u.`locked` = 0 AND `mobile` != ''";
        return $this->getConnection()->fetchColumn($sql, array(), 0);
    }

    public function findUserByVerifiedMobile($mobile)
    {
        $that = $this;

        return $this->fetchCached("mobile:{$mobile}", $mobile, function ($mobile) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE verifiedMobile = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($mobile));
        }

        );
    }

    public function findUsersByNicknames(array $nicknames)
    {
        if (empty($nicknames)) {
            return array();
        }

        $marks = str_repeat('?,', count($nicknames) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE nickname IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $nicknames);
    }

    public function findUsersByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $that = $this;
        $keys = implode(',', $ids);
        return $this->fetchCached("ids:{$keys}", $marks, $ids, function ($marks, $ids) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id IN ({$marks});";

            return $that->getConnection()->fetchAll($sql, $ids);
        }

        );
    }

    public function getUserByInviteCode($inviteCode)
    {
        $that = $this;

        return $this->fetchCached("inviteCode:{$inviteCode}", $inviteCode, function ($inviteCode) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE inviteCode = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($inviteCode)) ?: null;
        }

        );
    }

    public function searchUsers($conditions, $orderBys, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        for ($i = 0; $i < count($orderBys); $i = $i + 2) {
            $builder->addOrderBy($orderBys[$i], $orderBys[$i + 1]);
        };

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchUserCount($conditions)
    {
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function createUserQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($v) {
            if ($v === 0) {
                return true;
            }

            if (empty($v)) {
                return false;
            }

            return true;
        }

        );

        if (isset($conditions['roles'])) {
            $conditions['roles'] = "%{$conditions['roles']}%";
        }

        if (isset($conditions['role'])) {
            $conditions['role'] = "|{$conditions['role']}|";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if ($conditions['keywordType'] == 'loginIp') {
                $conditions[$conditions['keywordType']] = "{$conditions['keyword']}";
            } else {
                $conditions[$conditions['keywordType']] = "%{$conditions['keyword']}%";
            }

            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (isset($conditions['keywordUserType'])) {
            $conditions['type'] = "%{$conditions['keywordUserType']}%";
            unset($conditions['keywordUserType']);
        }

        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = "%{$conditions['nickname']}%";
        }

        if (!empty($conditions['datePicker']) && $conditions['datePicker'] == 'longinDate') {
            if (isset($conditions['startDate'])) {
                $conditions['loginStartTime'] = strtotime($conditions['startDate']);
            }

            if (isset($conditions['endDate'])) {
                $conditions['loginEndTime'] = strtotime($conditions['endDate']);
            }
        }

        if (!empty($conditions['datePicker']) && $conditions['datePicker'] == 'registerDate') {
            if (isset($conditions['startDate'])) {
                $conditions['startTime'] = strtotime($conditions['startDate']);
            }

            if (isset($conditions['endDate'])) {
                $conditions['endTime'] = strtotime($conditions['endDate']);
            }
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] = $conditions['likeOrgCode'].'%';
            unset($conditions['orgCode']);
        }

        $conditions['verifiedMobileNull'] = "";

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user')
            ->andWhere('promoted = :promoted')
            ->andWhere('roles LIKE :roles')
            ->andWhere('roles = :role')
            ->andWhere('UPPER(nickname) LIKE :nickname')
            ->andWhere('id =: id')
            ->andWhere('loginIp = :loginIp')
            ->andWhere('createdIp = :createdIp')
            ->andWhere('approvalStatus = :approvalStatus')
            ->andWhere('UPPER(email) LIKE :email')
            ->andWhere('level = :level')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('approvalTime >= :startApprovalTime')
            ->andWhere('approvalTime <= :endApprovalTime')
            ->andWhere('loginTime >= :loginStartTime')
            ->andWhere('loginTime <= :loginEndTime')
            ->andWhere('locked = :locked')
            ->andWhere('level >= :greatLevel')
            ->andWhere('UPPER(verifiedMobile) LIKE :verifiedMobile')
            ->andWhere('type LIKE :type')
            ->andWhere('id IN ( :userIds)')
            ->andWhere('inviteCode = :inviteCode')
            ->andWhere('inviteCode != :NoInviteCode')
            ->andWhere('id NOT IN ( :excludeIds )')
            ->andWhere('orgCode LIKE :likeOrgCode')
            ->andWhere('orgCode = :orgCode')
        ;

        if (array_key_exists('hasVerifiedMobile', $conditions)) {
            $builder = $builder->andWhere('verifiedMobile != :verifiedMobileNull');
        }

        return $builder;
    }

    public function addUser($user)
    {
        $user['createdTime'] = time();
        $user['updatedTime'] = $user['createdTime'];
        $affected            = $this->getConnection()->insert($this->table, $user);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert user error.');
        }

        return $this->getUser($this->getConnection()->lastInsertId());
    }

    public function updateUser($id, $fields)
    {
        // if (empty(array_diff(array_keys($fields), array(
        //     'loginIp', 
        //     'loginTime', 
        //     'updatedTime',
        //     'loginSessionId',
        //     'lockDeadline',
        //     'consecutivePasswordErrorTimes',
        //     'lastPasswordFailTime')))) {

        //     $fields['updatedTime'] = time();
        //     $user = $this->getUser($id);
        //     if($fields['updatedTime'] - $user['updatedTime'] > 1800) {
        //         $this->getConnection()->update($this->table, $fields, array('id' => $id));
        //         $this->clearCached();
        //     }
        //     return $user;

        // } else {
            $fields['updatedTime'] = time();
            $this->getConnection()->update($this->table, $fields, array('id' => $id));

            $this->clearCached();
            return $this->getUser($id);
        // }
    }

    public function waveCounterById($id, $name, $number)
    {
        $names = array('newMessageNum', 'newNotificationNum');

        if (!in_array($name, $names)) {
            throw $this->createDaoException('counter name error');
        }

        $currentTime = time();
        $sql         = "UPDATE {$this->table} SET {$name} = {$name} + ?, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";
        $result      = $this->getConnection()->executeQuery($sql, array($number, $id));
        $this->clearCached();
        return $result;
    }

    public function clearCounterById($id, $name)
    {
        $names = array('newMessageNum', 'newNotificationNum');

        if (!in_array($name, $names)) {
            throw $this->createDaoException('counter name error');
        }

        $currentTime = time();
        $sql         = "UPDATE {$this->table} SET {$name} = 0, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";
        $result      = $this->getConnection()->executeQuery($sql, array($id));
        $this->clearCached();
        return $result;
    }

    public function analysisRegisterDataByTime($startTime, $endTime)
    {
        $that = $this;

        return $this->fetchCached("startTime:{$startTime}:endTime:{$endTime}:count", $startTime, $endTime, function ($startTime, $endTime) use ($that) {
            $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$that->getTable()}` WHERE`createdTime`>=? AND `createdTime`<=? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";
            return $that->getConnection()->fetchAll($sql, array($startTime, $endTime));
        }

        );
    }

    public function analysisUserSumByTime($endTime)
    {
        $that = $this;

        return $this->fetchCached("endTime:{$endTime}:date:count", $endTime, function ($endTime) use ($that) {
            $sql = "select date, count(*) as count from (SELECT from_unixtime(o.createdTime,'%Y-%m-%d') as date from user o where o.createdTime<=? ) dates group by dates.date order by date desc";
            return $that->getConnection()->fetchAll($sql, array($endTime));
        }

        );
    }

    public function findUsersCountByLessThanCreatedTime($endTime)
    {
        $that = $this;

        return $this->fetchCached("endTime:{$endTime}:count", $endTime, function ($endTime) use ($that) {
            $sql = "SELECT count(id) as count FROM `{$that->getTable()}` WHERE  `createdTime`<=?  ";
            return $that->getConnection()->fetchColumn($sql, array($endTime));
        }

        );
    }
}
