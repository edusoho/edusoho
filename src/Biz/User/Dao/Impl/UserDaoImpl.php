<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserDaoImpl extends AdvancedDaoImpl implements UserDao
{
    protected $table = 'user';

    public function getByEmail($email)
    {
        return $this->getByFields(['email' => $email]);
    }

    public function getUserByType($type)
    {
        return $this->getByFields(['type' => $type]);
    }

    public function getByNickname($nickname)
    {
        return $this->getByFields(['nickname' => $nickname]);
    }

    public function getUnDestroyedUserByNickname($nickname)
    {
        return $this->getByFields(['nickname' => $nickname, 'destroyed' => 0]);
    }

    public function getByUUID($uuid)
    {
        return $this->getByFields(['uuid' => $uuid]);
    }

    public function countByMobileNotEmpty()
    {
        $sql = "SELECT COUNT(DISTINCT `mobile`) FROM `user` AS u, `user_profile` AS up WHERE u.id = up.id AND u.`locked` = 0 AND `mobile` != '' AND type <> 'system'";

        return $this->db()->fetchColumn($sql, [], 0);
    }

    public function findUnlockedUsersWithMobile($start, $limit)
    {
        $sql = "SELECT * FROM `user` AS u, `user_profile` AS up WHERE u.id = up.id AND u.`locked` = 0 AND `mobile` != '' AND type <> 'system'";

        $sql = $this->sql($sql, ['createdTime' => 'ASC'], $start, $limit);

        return $this->db()->fetchAll($sql);
    }

    public function getByVerifiedMobile($mobile)
    {
        return $this->getByFields(['verifiedMobile' => $mobile]);
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
        return $this->getByFields(['inviteCode' => $inviteCode]);
    }

    public function waveCounterById($id, $name, $number)
    {
        $names = ['newMessageNum', 'newNotificationNum'];

        if (!in_array($name, $names)) {
            return [];
        }

        return $this->wave([$id], [$name => $number]);
    }

    public function deleteCounterById($id, $name)
    {
        $names = ['newMessageNum', 'newNotificationNum'];

        if (!in_array($name, $names)) {
            return [];
        }

        $currentTime = time();
        $sql = "UPDATE {$this->table} SET {$name} = 0, updatedTime = '{$currentTime}' WHERE id = ? LIMIT 1";

        return $this->db()->executeQuery($sql, [$id]);
    }

    public function analysisRegisterDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE `createdTime`>=? AND `createdTime`<=? AND type <> 'system' group by date order by date ASC ";

        return $this->db()->fetchAll($sql, [$startTime, $endTime]);
    }

    public function countByLessThanCreatedTime($time)
    {
        $sql = "SELECT count(id) as count FROM `{$this->table()}` WHERE  `createdTime` <= ? and type <> 'system' ";

        return $this->db()->fetchColumn($sql, [$time]);
    }

    public function findUnDestroyedUsersByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE destroyed = 0 AND id IN ({$marks});";

        return $this->db()->fetchAll($sql, $ids);
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ('0' == $value) {
                return true;
            }

            if (empty($value)) {
                return false;
            }

            return true;
        });

        if (isset($conditions['role'])) {
            $conditions['role'] = "|{$conditions['role']}|";
        }

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            if ('loginIp' == $conditions['keywordType']) {
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

        if (!empty($conditions['datePicker']) && 'longinDate' == $conditions['datePicker']) {
            if (isset($conditions['startDate'])) {
                $conditions['loginStartTime'] = strtotime($conditions['startDate']);
            }

            if (isset($conditions['endDate'])) {
                $conditions['loginEndTime'] = strtotime($conditions['endDate']);
            }
        }

        if (!empty($conditions['datePicker']) && 'registerDate' == $conditions['datePicker']) {
            if (isset($conditions['startDate'])) {
                $conditions['startTime'] = strtotime($conditions['startDate']);
            }

            if (isset($conditions['endDate'])) {
                $conditions['endTime'] = strtotime($conditions['endDate']);
            }
        }

        $conditions['verifiedMobileNull'] = '';

        $builder = parent::createQueryBuilder($conditions);
        if (array_key_exists('hasVerifiedMobile', $conditions)) {
            $builder->andStaticWhere("verifiedMobile != ''");
        }

        $builder->andStaticWhere("type <> 'system'");

        return $builder;
    }

    public function searchUsersJoinUserFace($conditions, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('user.*')
            ->setFirstResult($start)
            ->setMaxResults($limit);
        $builder = $this->buildJoinCondition($builder, $conditions);

        return $builder->execute()->fetchAll();
    }

    public function countUsersJoinUserFace($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('COUNT(user.id)');
        $builder = $this->buildJoinCondition($builder, $conditions);

        return $builder->execute()->fetchColumn();
    }

    protected function buildJoinCondition($builder, $conditions)
    {
        if (isset($conditions['faceStatus'])) {
            if ('capture' == $conditions['faceStatus']) {
                $builder->andStaticWhere('id in (select user_id from user_face )');
            }
            if ('noCapture' == $conditions['faceStatus']) {
                $builder->andStaticWhere('id not in (select user_id from user_face )');
            }
        }

        return $builder;
    }

    public function declares()
    {
        return [
            'serializes' => [
                'roles' => 'delimiter',
            ],
            'orderbys' => [
                'id',
                'createdTime',
                'updatedTime',
                'promotedTime',
                'promoted',
                'promotedSeq',
                'nickname',
                'loginTime',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'mobile = :mobile',
                'promoted = :promoted',
                'roles LIKE :roles',
                'roles = :role',
                'UPPER(nickname) LIKE :nickname',
                'id = :id',
                'id > :id_GT',
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
                'verifiedMobile = :wholeVerifiedMobile',
                'type LIKE :type',
                'id IN ( :userIds)',
                'inviteCode = :inviteCode',
                'inviteCode != :NoInviteCode',
                'id NOT IN ( :excludeIds )',
                'orgCode PRE_LIKE :likeOrgCode',
                'orgCode = :orgCode',
                'distributorToken = :distributorToken',
                'destroyed = :destroyed',
            ],
        ];
    }
}
