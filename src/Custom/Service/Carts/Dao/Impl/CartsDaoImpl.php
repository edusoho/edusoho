<?php

namespace Custom\Service\Carts\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Carts\Dao\CartsDao;

class CartsDaoImpl extends BaseDao implements CartsDao
{
    protected $table = 'carts';

    public function getCarts($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addCarts(array $carts)
    {
        $affected = $this->getConnection()->insert($this->table, $carts);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert carts error.');
        }
        return $this->getCarts($this->getConnection()->lastInsertId());
    }

    public function updateCarts($id, array $carts)
    {
        $this->getConnection()->update($this->table, $carts, array('id' => $id));
        return $this->getCarts($id);
    }

    public function deleteCarts($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function searchCarts($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->select('*')
            ->from($this->table, $this->table)
            ->andWhere('userId = :userId')
            ->andWhere('itemType = :itemType')
            ->addOrderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchCartsCount(array $conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->select('COUNT(id)')
            ->from($this->table, $this->table)
            ->andWhere('userId = :userId');
        return $builder->execute()->fetchColumn(0);
    }

}