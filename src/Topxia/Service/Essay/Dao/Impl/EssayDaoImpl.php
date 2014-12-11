<?php

namespace Topxia\Service\Essay\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Essay\Dao\EssayDao;

class EssayDaoImpl extends BaseDao implements EssayDao
{
    protected $table = 'essay';

    public function getEssay($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addEssay(array $essay)
    {
        $affected = $this->getConnection()->insert($this->table, $essay);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert essay error.');
        }
        return $this->getEssay($this->getConnection()->lastInsertId());
    }

    public function updateEssay($id, array $essay)
    {
        $this->getConnection()->update($this->table, $essay, array('id' => $id));
        return $this->getEssay($id);
    }

    public function deleteEssay($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function searchEssays(array $conditions, array $orderBy, $start, $limit)
    {
        if(isset($conditions['keyword'])){
            $conditions['title'] = "%{$conditions['keyword']}%";
            unset($conditions['keyword']);
        }

        $this->filterStartLimit($start, $limit);
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->select('*')
            ->from($this->table, $this->table)
            ->andWhere('status = :status')
            ->andWhere('title LIKE :title')
            ->addOrderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchEssaysCount(array $conditions)
    {
        if(isset($conditions['title'])){
            $conditions['title'] = "%{$conditions['title']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->select('COUNT(id)')
            ->from($this->table, $this->table)
            ->andWhere('status = :status')
            ->andWhere('title LIKE :title');
        return $builder->execute()->fetchColumn(0);
    }

}