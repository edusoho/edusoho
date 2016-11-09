<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagGroupDao;

class TagGroupDaoImpl extends BaseDao implements TagGroupDao
{
    protected $table = 'tag_group';

    private $serializeFields = array(
        'scope' => 'saw'
    );

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $tagGroup = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        return $tagGroup ? $this->createSerializer()->unserialize($tagGroup, $this->serializeFields) : array();
    }

    public function findTagGroupByName($name)
    {        
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        $tagGroup = $this->getConnection()->fetchAssoc($sql, array($name)) ?: null;
        return $tagGroup ? $this->createSerializer()->unserialize($tagGroup, $this->serializeFields) : array();
    }

    public function findTagGroups()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC";    
        $tagGroups = $this->getConnection()->fetchAll($sql, array()) ?: array();
        return $tagGroups ? $this->createSerializer()->unserializes($tagGroups, $this->serializeFields) : array();
    }

    public function findTagGroupsByGroupIds($groupIds)
    {
        if(empty($groupIds)){
            return array();
        }

        $marks = str_repeat('?,', count($groupIds) - 1) . '?';

        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $groupIds);
    }

    public function create($fields)
    {   
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }

        $this->clearCached();
        return $this->get($this->getConnection()->lastInsertId());
    }

    public function delete($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function update($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->get($id);
    }
}
