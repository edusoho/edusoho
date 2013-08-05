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

    public function searchUsers($conditions, $start, $limit)
    {
        if (isset($conditions['roles'])) {
            $conditions['roles'] = "%{$conditions['roles']}%";
        }
        
        if (isset($conditions['nicknameLike'])) {
            $conditions['nicknameLike'] = "%{$conditions['nicknameLike']}%";
        }
        
        if(isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']]=$conditions['keyword'];
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->select('*')
            ->from($this->table, 'user')
            ->andWhere('roles LIKE :roles')
            ->andWhere('nickname = :nickname')
            ->andWhere('nickname LIKE :nicknameLike')
            ->andWhere('loginIp = :loginIp')
            ->andWhere('email = :email')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array();  
    }

    public function searchUserCount($conditions)
    {
        if (isset($conditions['roles'])) {
            $conditions['roles'] = "%{$conditions['roles']}%";
        }
        $conditions[$conditions['keywordType']]=$conditions['keyword'];

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->select('count(id)')
            ->from($this->table, 'user')
            ->andWhere('roles LIKE :roles')
            ->andWhere('nickname = :nickname')
            ->andWhere('loginIp = :loginIp')
            ->andWhere('email = :email');

        return $builder->execute()->fetchColumn(0);
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
        $sql = "UPDATE {$this->table} SET coin = coin + ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff));
    }

    public function waveUnreadNotificationNum ($id, $diff) 
    {
        $sql = "UPDATE {$this->table} SET unreadNotificationNum = unreadNotificationNum + ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff));
    }

    public function wavePoint($id, $point)
    {
        $sql = "UPDATE {$this->table} SET point = point + ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($point));
    }

}