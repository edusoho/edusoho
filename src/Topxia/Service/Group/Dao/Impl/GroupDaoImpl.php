<?php
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\GroupDao;

class GroupDaoImpl extends BaseDao implements GroupDao
{

    protected $table = 'groups';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function searchGroupsCount($conditions)
    {
        $builder = $this->_createGroupSearchBuilder($conditions)
                         ->select('count(id)');
        return $builder->execute()->fetchColumn(0); 
    }

    public function waveGroup($id, $field, $diff)
    {
        $fields = array('postNum', 'threadNum','memberNum');
        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function getGroup($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addGroup($group)
    {
        $group = $this->createSerializer()->serialize($group, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $group);
        if ($affected <= 0) {

            throw $this->createDaoException('Insert Group error.');
        }

        return $this->getGroup($this->getConnection()->lastInsertId());
    }

     
    public function updateGroup($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getGroup($id);
    }
 
   
    public function searchGroups($conditions,$orderBy,$start,$limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createGroupSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);
  
        return $builder->execute()->fetchAll() ? : array();  
    }


    public function getGroupsByIds($ids)
    {
        if(empty($ids)) { 
            return array(); 
        }

        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getGroupByTitle($title)
    {
        $sql="SELECT * FROM {$this->table} WHERE title=? ";

        return $this->getConnection()->fetchAll($sql,array($title)) ? : array();

    }

    private function _createGroupSearchBuilder($conditions)
    {

        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('ownerId=:ownerId')
            ->andWhere('status = :status')
            ->andWhere('title like :title');

        return $builder;
    }

}