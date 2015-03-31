<?php

namespace Custom\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\User\Dao\CustomUserDao;
use Topxia\Common\DaoException;
use PDO;

class CustomUserDaoImpl extends BaseDao implements CustomUserDao
{
    protected $table = 'user';

    public function searchUsers($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
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
        $conditions = array_filter($conditions,function($v){
            if($v === 0){
                return true;
            }
                
            if(empty($v)){
                return false;
            }
            return true;
        });
        if (isset($conditions['roles'])) {
            $conditions['roles'] = "%{$conditions['roles']}%";
        }

        $conditions['tagsLike']="";
        if (isset($conditions['gradeTag'])) {
            $tagId = $conditions['gradeTag'];
            $conditions['tagsLike'] .= '%|';
            if (!empty($tagId)) {
                $conditions['tagsLike'] .= "{$tagId}|";
            }
            $conditions['tagsLike'] .= '%';
            unset($conditions['gradeTag']);
        }

         if (isset($conditions['subjectTag'])) {
            $tagId = $conditions['subjectTag'];
            $conditions['tagsLike'] = '%|';
            if (!empty($tagId)) {
                $conditions['tagsLike'] .= "{$tagId}|";
            }
            $conditions['tagsLike'] .= '%';
            unset($conditions['subjectTag']);
        }
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user')
            ->andWhere('promoted = :promoted')
            ->andWhere('roles LIKE :roles')
            ->andWhere('tags LIKE :tagsLike')
            ->andWhere('roles = :role')
            ->andWhere('locked = :locked')
            ->andWhere('level >= :greatLevel');
          
    }

 











 
}