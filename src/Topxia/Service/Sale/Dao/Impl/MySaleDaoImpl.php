<?php
namespace Topxia\Service\Sale\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Sale\Dao\MySaleDao;

class MySaleDaoImpl extends BaseDao implements MySaleDao
{

    protected $table = 'mysale';

    public function getMySale($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findMySalesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchMySales($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchMySaleCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function addMySale($mysale)
    {
        $affected = $this->getConnection()->insert($this->table, $mysale);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  mysale error.');
        }
        return $this->getMySale($this->getConnection()->lastInsertId());
    }

    public function updateMySale($id, $mysale)
    {
        $this->getConnection()->update($this->table, $mysale, array('id' => $id));
        return $this->getMySale($id);
    }

    public function deleteMySale($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }


    public function getMySaleBymTookeen($mTookeen)
    {
        $sql = "SELECT * FROM {$this->table} WHERE mTookeen = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($mTookeen)) ? : null;
    }


    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['prodName'])) {
            $conditions['prodNameLike'] = "%{$conditions['prodName']}%";
            unset($conditions['prodName']);
        }

        if (isset($conditions['mTookeen'])) {
            $conditions['mTookeenLike'] = "%{$conditions['mTookeen']}%";
            unset($conditions['mTookeen']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from(self::TABLENAME, 'mysale')
            ->andWhere('prodType = :prodType')
            ->andWhere('prodId = :prodId')
            ->andWhere('prodName LIKE :prodNameLike')
            ->andWhere('mTookeen LIKE :mTookeenLike')
            ->andWhere('userId = :userId')
            ->andWhere('managerId = :managerId')
            ->andWhere('validTime >= :startTimeGreaterThan')
            ->andWhere('validTime < :startTimeLessThan');

        

        return $builder;
    }

    private function getTablename()
    {
        return self::TABLENAME;
    }

   

}