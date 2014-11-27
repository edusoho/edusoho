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

    public function updateTagToDisabled($id)
    {
        $this->getConnection()->update($this->table, array('disabled' => 1), array('id' => $id));
        return $this->getTag2($id);
    }

    public function updateTag2sByGroupId($groupId,$newGroupId)
    {
        $this->getConnection()->update($this->table, array('groupId' => $newGroupId), array('groupId' => $groupId));
    }

    public function updateTagToDisabledByGroupId($groupId)
    {
        $this->getConnection()->update($this->table, array('disabled' => 1), array('groupId' => $groupId));
    }

    public function findTag2sByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findAllTags()
    {
        $sql ="SELECT * FROM {$this->table} WHERE `disabled` = 0";
        return $this->getConnection()->fetchAll($sql);
    }

    public function findTag2sByNames(array $names)
    {
        if(empty($names)){ return array(); }
        $marks = str_repeat('?,', count($names) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE name IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $names);
    }

    public function findTagsByTagGroupIds(array $groupIds)
    {
        if(empty($groupIds)){ return array(); }
        $marks = str_repeat('?,', count($groupIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE groupId IN ({$marks}) AND disabled = 0;";
        return $this->getConnection()->fetchAll($sql, $groupIds);
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
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ? AND disabled = 0";
        return $this->getConnection()->fetchAll($sql, array($name));
    }

}