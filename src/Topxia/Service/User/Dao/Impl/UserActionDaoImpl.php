<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserActionDao;
use Topxia\Common\DaoException;
use PDO;

class UserActionDaoImpl extends BaseDao implements UserActionDao
{
    protected $table = 'user_action';

    public function getUser($id)
    {
        return $this->fetch($id);
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

    public function searchUsers($conditions,array $orderBy, $start, $limit)
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
        
        if (isset($conditions['nicknameLike'])) {
            $conditions['nicknameLike'] = "%{$conditions['nicknameLike']}%";
        }
        
        if(isset($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']]=$conditions['keyword'];
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user')
            ->andWhere('promoted = :promoted')
            ->andWhere('roles LIKE :roles')
            ->andWhere('nickname = :nickname')
            ->andWhere('nickname LIKE :nicknameLike')
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

   

}