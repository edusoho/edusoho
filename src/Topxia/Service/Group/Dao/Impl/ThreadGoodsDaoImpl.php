<?php
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadGoodsDao;

class ThreadGoodsDaoImpl extends BaseDao implements ThreadGoodsDao
{

    protected $table = 'groups_thread_goods';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getGoods($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addGoods($Goods)
    {
        $Goods = $this->createSerializer()->serialize($Goods, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $Goods);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert ThreadGoods error.');
        }

        return $this->getGoods($this->getConnection()->lastInsertId());
    }

    public function waveGoods($id, $field, $diff)
    {
        $fields = array('hitNum');
        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function updateGoods($id,$fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getGoods($id);
    }

    public function deleteGoodsByThreadId($id,$type)
    {
        $sql ="DELETE FROM {$this->table} WHERE threadId = ? and type = ? ";
        return $this->getConnection()->executeUpdate($sql, array($id,$type));
    }

    public function deleteGoods($id)
    {
        $sql ="DELETE FROM {$this->table} WHERE id = ? ";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function sumGoodsCoins($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(coin)');
        return $builder->execute()->fetchColumn(0);
    }

    public function waveGoodsHitNum($goodsId)
    {
        $sql = "UPDATE {$this->table} SET hitnum = hitnum + 1 WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($goodsId));
    }

    public function searchGoods($conditions,$orderBy,$start,$limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->createQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);
  
        return $builder->execute()->fetchAll() ? : array();  
    }

    private function createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'groups_thread_goods')
            ->andWhere('threadId = :threadId')
            ->andWhere('fileId = :fileId')
            ->andWhere('postId = :postId')
            ->andWhere('type = :type')
            ;
    }
}