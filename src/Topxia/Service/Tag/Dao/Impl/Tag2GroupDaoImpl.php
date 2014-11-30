<?php

namespace Topxia\Service\Tag\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Tag\Dao\Tag2GroupDao;

class Tag2GroupDaoImpl extends BaseDao implements Tag2GroupDao
{
    protected $table = 'tag_group';

    public function getTag2Group($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addTag2Group(array $tag)
    {
        $affected = $this->getConnection()->insert($this->table, $tag);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }
        return $this->getTag2Group($this->getConnection()->lastInsertId());
    }

    public function updateTag2Group($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getTag2Group($id);
    }

    public function updateTagGroupToDisabled($id)
    {
        $this->getConnection()->update($this->table, array('disabled' => 1), array('id' => $id));
        return $this->getTag2Group($id);
    }

    public function findTag2GroupsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks}) AND disabled = 0;";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findTag2GroupsByNames(array $names)
    {
        if(empty($names)){ return array(); }
        $marks = str_repeat('?,', count($names) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE name IN ({$marks}) AND disabled = 0;";
        return $this->getConnection()->fetchAll($sql, $names);
    }

    public function findAllTagGroups()
    {
        $sql ="SELECT * FROM {$this->table} WHERE `disabled` = 0";
        return $this->getConnection()->fetchAll($sql);
    }

    public function findAllTag2Groups($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table}  WHERE disabled = 0 ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function getTag2GroupByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND disabled = 0 LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
    }

    public function getDisabledTag2GroupByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND disabled = 1 LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
    }


    public function getTag2GroupByLikeName($name)
    {
        $name = "%{$name}%";
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ? AND disabled = 0";
        return $this->getConnection()->fetchAll($sql, array($name));
    }

    public function findAllTag2GroupsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE disabled = 0";
        return $this->getConnection()->fetchColumn($sql, array());
    }

}