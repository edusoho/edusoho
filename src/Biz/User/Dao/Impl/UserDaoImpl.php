<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Topxia\Service\Common\DynamicQueryBuilder;

class UserDaoImpl extends GeneralDaoImpl implements UserDao
{
    protected $table = 'user';

    public function getByEmail($email)
    {
        return $this->getByFields(array('email' => $email));
    }

    public function getByNickname($nickname)
    {
        return $this->getByFields(array('nickname' => $nickname));
    }

    public function countByMobileNotEmpty()
    {
        $sql = "SELECT COUNT(DISTINCT `mobile`) FROM `user` AS u, `user_profile` AS up WHERE u.id = up.id AND u.`locked` = 0 AND `mobile` != ''";
        return $this->db()->fetchColumn($sql, array(), 0);
    }

    public function getByVerifiedMobile($mobile)
    {
        return $this->getByFields(array('verifiedMobile' => $mobile));
    }

    public function findByNicknames(array $nicknames)
    {
        return $this->findInField('nickname', $nicknames);
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByInviteCode($inviteCode)
    {
        return $this->getByFields(array('inviteCode' => $inviteCode));
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys as $field => $direction) {
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function count($conditions)
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
        } else {
            if (isset($conditions['keywordUserType'])) {
                $conditions['type'] = "%{$conditions['keywordUserType']}%";
                unset($conditions['keywordUserType']);
            }
            if (isset($conditions['nickname'])) {
                $conditions['nickname'] = "%{$conditions['nickname']}%";
            }
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

        $builder = new DynamicQueryBuilder($this->db(), $conditions);
        $builder->from($this->table, 'user')
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

    public function waveCounterById($id, $name, $number)
    {
        $names = array('newMessageNum', 'newNotificationNum');

        if (!in_array($name, $names)) {
            throw $this->createDaoException('counter name error');
        }

        $currentTime = time();
        $sql         = "UPDATE {$this->table} SET {$name} = {$name} + ?, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";
        $result      = $this->db()->executeQuery($sql, array($number, $id));
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
        $result      = $this->db()->executeQuery($sql, array($id));
        return $result;
    }

    public function analysisRegisterDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? AND `createdTime`<=? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function analysisUserSumByTime($endTime)
    {
        $sql = "select date, count(*) as count from (SELECT from_unixtime(o.createdTime,'%Y-%m-%d') as date from user o where o.createdTime<=? ) dates group by dates.date order by date desc";
        return $this->db()->fetchAll($sql, array($endTime));
    }

    public function countByLessThanCreatedTime($endTime)
    {
        $sql = "SELECT count(id) as count FROM `{$this->table}` WHERE  `createdTime`<=?  ";
        return $this->db()->fetchColumn($sql, array($endTime));
    }

    public function declares()
    {
        return array(
            'timestamps' => array(
                'createdTime',
                'updatedTime'
            ),
            'conditions' => array(
                'nickname = :nickname',
                'email = :email',
                'mobile = :mobile'
            )
        );
    }
}
