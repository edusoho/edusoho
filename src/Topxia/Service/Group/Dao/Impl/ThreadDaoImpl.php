<?php

namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\ThreadDao;

class ThreadDaoImpl extends BaseDao implements ThreadDao 
{
    protected $table = 'groups_thread';
    private $serializeFields = array(
        'tagIds' => 'json',
    );
    public function getThread($id)
    {
        $sql="SELECT * from {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql,array($id)) ? : null;

    }

    public function getThreadsByIds($ids)
    {
        if(empty($ids)) { 
            return array(); 
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function addThread($thread)
    {
    	$thread = $this->createSerializer()->serialize($thread, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $thread);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Thread error.');
        }

        return $this->getThread($this->getConnection()->lastInsertId());
    }
 

    public function searchThreadsCount($conditions)
    {
        $builder = $this->_createThreadSearchBuilder($conditions)
            ->select('count(id)');
        return $builder->execute()->fetchColumn(0); 
    }
    
    public function waveThread($id, $field, $diff) 
    {
        $fields = array('postNum','hitNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));

    }

    public function updateThread($id,$fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getThread($id);
    }

    public function deleteThread($id)
    {
        $this->getConnection()->delete($this->table,array('id'=>$id));
        
    }

    public function searchThreads($conditions,$orderBys,$start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createThreadSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);
            foreach ($orderBys as $orderBy) 
        {
            $builder->addOrderBy($orderBy[0], $orderBy[1]);
        };
 
        return $builder->execute()->fetchAll() ? : array();  
    }

    private function _createThreadSearchBuilder($conditions)
    {
        if (isset($conditions['title'])) 
        {
            $conditions['title'] = "%{$conditions['title']}%";

        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('groupId = :groupId') 
            ->andWhere('createdTime > :createdTime')
            ->andWhere('isElite = :isElite')
            ->andWhere('isStick = :isStick')
            ->andWhere('type = :type')
            ->andWhere('userId = :userId')
            ->andWhere('status = :status')
            ->andWhere('title like :title'); 
        return $builder;
    }
}