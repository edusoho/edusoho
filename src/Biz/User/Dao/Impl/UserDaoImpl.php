<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

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

    public function waveCounterById($id, $name, $number)
    {
        $names = array('newMessageNum', 'newNotificationNum');

        if (!in_array($name, $names)) {
            return array();
        }

        return $this->wave(array($id), array($name => $number));
    }

    public function clearCounterById($id, $name)
    {
        $names = array('newMessageNum', 'newNotificationNum');

        if (!in_array($name, $names)) {
            return array();
        }

        $currentTime = time();
        $sql         = "UPDATE {$this->table} SET {$name} = 0, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";
        return $this->db()->executeQuery($sql, array($id));
    }

    public function analysisRegisterDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE`createdTime`>=? AND `createdTime`<=? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";
        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function countUserNumDueTime($time)
    {
        $sql = "SELECT count(*) AS count FROM (SELECT from_unixtime(createdTime, '%Y-%m-%d') AS date FROM {$this->table} 
            WHERE createdTime <= ?) AS sums ORDER BY date DESC";
        return $this->db()->fetchColumn($sql, array($time));
    }

    public function countByLessThanCreatedTime($time)
    {
        $sql = "SELECT count(id) as count FROM `{$this->table()}` WHERE  `createdTime` <= ?  ";
        return $this->db()->fetchColumn($sql, array($time));
    }

    //replace: count(array('createdTime' => $endTime))
    // public function countByLessThanCreatedTime($endTime)
    // {
    //     $sql = "SELECT count(id) as count FROM `{$this->table}` WHERE  `createdTime`<=?  ";
    //     return $this->db()->fetchColumn($sql, array($endTime));
    // }

    protected function _createQueryBuilder($conditions)
    {
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

        $builder = parent::_createQueryBuilder($conditions);

        if (array_key_exists('hasVerifiedMobile', $conditions)) {
            $builder = $builder->andWhere('verifiedMobile != :verifiedMobileNull');
        }

        return $builder;
    }

    public function declares()
    {
        return array(
            'orderbys'   => array(
                'id', 
                'createdTime', 
                'promotedTime', 
                'promoted', 
                'promotedSeq', 
                'nickname'
            ),
            'timestamps' => array(
                'createdTime',
                'updatedTime'
            ),
            'conditions' => array(
                'mobile = :mobile',
                'promoted = :promoted',
                'roles LIKE :roles',
                'roles = :role',
                'UPPER(nickname) LIKE :nickname',
                'id =: id',
                'loginIp = :loginIp',
                'createdIp = :createdIp',
                'approvalStatus = :approvalStatus',
                'UPPER(email) LIKE :email',
                'level = :level',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'updatedTime >= :updatedTime_GE',
                'approvalTime >= :startApprovalTime',
                'approvalTime <= :endApprovalTime',
                'loginTime >= :loginStartTime',
                'loginTime <= :loginEndTime',
                'locked = :locked',
                'level >= :greatLevel',
                'UPPER(verifiedMobile) LIKE :verifiedMobile',
                'type LIKE :type',
                'id IN ( :userIds)',
                'inviteCode = :inviteCode',
                'inviteCode != :NoInviteCode',
                'id NOT IN ( :excludeIds )',
                'orgCode LIKE :likeOrgCode',
                'orgCode = :orgCode'
            )
        );
    }
}
