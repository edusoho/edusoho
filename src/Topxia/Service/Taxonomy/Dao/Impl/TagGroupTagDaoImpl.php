<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagGroupTagDao;

class TagGroupTagDaoImpl extends BaseDao implements TagGroupTagDao
{
    protected $table = 'tag_group_tag';

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: array();
    }

    public function findTagRelationsByTagIds($tagIds)
    {
        if(empty($tagIds)){
            return array();
        }

        $marks = str_repeat('?,', count($tagIds) - 1) . '?';

        $sql ="SELECT * FROM {$this->table} WHERE tagId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $tagIds) ?: array();
    }

    public function findTagRelationsByTagId($tagId)
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE tagId = ?";
        return $this->getConnection()->fetchAll($sql, array($tagId)) ?: array();
    }

    public function create($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert tagGroup error.');
        }
        $this->clearCached();
        return $this->get($this->getConnection()->lastInsertId());

    }

    public function findTagRelationsByGroupId($groupId)
    {
        $that = $this;

        return $this->fetchCached("groupId:{$groupId}", $groupId, function ($groupId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE groupId = ?";
            return $that->getConnection()->fetchAll($sql, array($groupId)) ?: array();
        }

        );
    }

    public function update($groupId, $fields)
    {
        $this->getConnection->update($this->table, $fieleds, array('groupId' => $groupId));
        $this->clearCached();
        return $this->get($groupId);
    }

    public function deleteByGroupId($groupId)
    {
        $result = $this->getConnection()->delete($this->table, array('groupId' => $groupId));
        $this->clearCached();
        return $result;
    }

    public function deleteByGroupIdAndTagId($groupId, $tagId)
    {
        $result = $this->getConnection()->delete($this->table, array('groupId' => $groupId, 'tagId' => $tagId));
        $this->clearCached();
        return $result;
    }
}
