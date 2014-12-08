<?php
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadHideDao;

class ThreadHideDaoImpl extends BaseDao implements ThreadHideDao
{

    protected $table = 'groups_thread_hide';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getHide($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addHide($hide)
    {
        $hide = $this->createSerializer()->serialize($hide, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $hide);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert ThreadHide error.');
        }

        return $this->getHide($this->getConnection()->lastInsertId());
    }

    public function waveHide($id, $field, $diff)
    {
        $fields = array('hitNum');
        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function updateHide($id,$fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getHide($id);
    }

    public function deleteHideByThreadId($id,$type)
    {
        $sql ="DELETE FROM {$this->table} WHERE threadId = ? and type = ? ";
        return $this->getConnection()->executeUpdate($sql, array($id,$type));
    }

    public function deleteHide($id)
    {
        $sql ="DELETE FROM {$this->table} WHERE id = ? ";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function getCoinByThreadId($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(coin)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchHides($conditions,$orderBy,$start,$limit)
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
            ->from($this->table, 'groups_thread_hide')
            ->andWhere('threadId = :threadId')
            ->andWhere('fileId = :fileId')
            ->andWhere('type = :type')
            ;
    }
}