<?php

namespace Topxia\Service\Tag\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Tag\Dao\Tag2Dao;

class Tag2DaoImpl extends BaseDao implements Tag2Dao
{
    protected $table = 'tag2';

    public function getTag2($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addTag2(array $tag)
    {
        $affected = $this->getConnection()->insert($this->table, $tag);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }
        return $this->getTag2($this->getConnection()->lastInsertId());
    }

    public function updateTag2($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getTag2($id);
    }

    public function findTag2sByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findTag2sByNames(array $names)
    {
        if(empty($names)){ return array(); }
        $marks = str_repeat('?,', count($names) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE name IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $names);
    }

    public function findTagsByTagGroupIds(array $tagGroupIds)
    {
        if(empty($tagGroupIds)){ return array(); }
        $marks = str_repeat('?,', count($tagGroupIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE groupId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $tagGroupIds);
    }

    public function findAllTag2s($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function getTag2ByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND disabled = 0 LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
    }

    public function getDisabledTag2ByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND disabled = 1 LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
    }

    public function getTag2ByLikeName($name)
    {
        $name = "%{$name}%";
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ?";
        return $this->getConnection()->fetchAll($sql, array($name));
    }

    public function findAllTag2sCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} ";
        return $this->getConnection()->fetchColumn($sql, array());
    }

}