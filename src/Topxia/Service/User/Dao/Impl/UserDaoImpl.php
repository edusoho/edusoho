<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserDao;
use Topxia\Common\DaoException;
use PDO;

class UserDaoImpl extends BaseDao implements UserDao
{
    protected $table = 'user';

    public function getUser($id)
    {
        return $this->fetch($id);
    }

    public function findUserByEmail($email)
    {
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'user')
            ->where("email = :email")
            ->orderBy('createdTime', 'DESC')
            ->setParameter(":email", $email)
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
    }

    public function findUserByNickname($nickname)
    {
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'user')
            ->where("nickname = :nickname")
            ->orderBy('createdTime', 'DESC')
            ->setParameter(":nickname", $nickname)
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
    }

    public function findUsersByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchUsers($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();  
    }

    public function searchUserCount($conditions)
    {
        $builder = $this->createUserQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    private function createUserQueryBuilder($conditions)
    {
        if (isset($conditions['roles'])) {
            $conditions['roles'] = "%{$conditions['roles']}%";
        }

        if(isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']]=$conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (isset($conditions['nickname'])) {
            $conditions['nickname'] = "%{$conditions['nickname']}%";
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user')
            ->andWhere('promoted = :promoted')
            ->andWhere('roles LIKE :roles')
            ->andWhere('nickname LIKE :nickname')
            ->andWhere('loginIp = :loginIp')
            ->andWhere('email = :email');
    }

    public function addUser($user)
    {
        $id = $this->insert($user);
        return $this->getUser($id);
    }

    public function updateUser($id, $fields)
    {
        return $this->update($id, $fields);
    }

    public function waveCoin($id, $diff)
    {
        $sql = "UPDATE {$this->table} SET coin = coin + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function wavePoint($id, $point)
    {
        $sql = "UPDATE {$this->table} SET point = point + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($point, $id));
    }

    public function waveCounterById($id, $name, $number)
    {
        $names = array('newMessageNum', 'newNotificationNum');
        if (!in_array($name, $names)) {
            throw $this->createDaoException('counter name error');
        }
        $sql = "UPDATE {$this->table} SET {$name} = {$name} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($number, $id));
    }

    public function clearCounterById($id, $name)
    {
        $names = array('newMessageNum', 'newNotificationNum');
        if (!in_array($name, $names)) {
            throw $this->createDaoException('counter name error');
        }
        $sql = "UPDATE {$this->table} SET {$name} = 0 WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($id));
    }

}