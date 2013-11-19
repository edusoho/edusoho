<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserlevelDao;
use Topxia\Common\DaoException;
use PDO;

class UserlevelDaoImpl extends BaseDao implements UserlevelDao
{
	protected $table = 'user_level';

    public function getUserlevel($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getUserlevelByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE Name = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name)) ? : null;
    }

	public function searchUserlevels($conditions, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createUserlevelSearchQueryBuilder($conditions)
        ->select('*')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->orderBy('seq','ASC');
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchUserlevelsCount($conditions)
    {
        $builder = $this->createUserlevelSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function createUserlevel($userlevel)
    {
        $affected = $this->getConnection()->insert($this->table, $userlevel);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert userlevel post error.');
        }
        return $this->getUserlevel($this->getConnection()->lastInsertId());
    }

    public function updateUserlevel($id,$fields)
     {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getUserlevel($id);
    }

    public function deleteUserlevel($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    private function createUserlevelSearchQueryBuilder($conditions)
    {
        
        $builder = $this->createDynamicQueryBuilder($conditions)
        ->from($this->table, 'user_level')
        ->andWhere('id = :id')
        ->andWhere('levelName LIKE :Name')
        ->andWhere('levelIcon = :Icon');

        return $builder;
    }

}

