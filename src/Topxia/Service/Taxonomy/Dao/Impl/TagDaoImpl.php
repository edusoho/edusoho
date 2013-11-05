<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagDao;

class TagDaoImpl extends BaseDao implements TagDao
{
    protected $table = 'tag';

    public function getTag($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addTag(array $tag)
    {
        $affected = $this->getConnection()->insert($this->table, $tag);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }
        return $this->getTag($this->getConnection()->lastInsertId());
    }

    public function updateTag($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getTag($id);
    }

    public function deleteTag($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function findTagsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findTagsByNames(array $names)
    {
        if(empty($names)){ return array(); }
        $marks = str_repeat('?,', count($names) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE name IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $names);
    }

    public function findAllTags($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function getTagByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($name));
    }

    public function getTagByLikeName($name)
    {
        $name = "%{$name}%";
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ?";
        return $this->getConnection()->fetchAll($sql, array($name));
    }

    public function findAllTagsCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} ";
        return $this->getConnection()->fetchColumn($sql, array());
    }

}