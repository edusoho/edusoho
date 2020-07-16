<?php

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\TagOwnerDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TagOwnerDaoImpl extends AdvancedDaoImpl implements TagOwnerDao
{
    protected $table = 'tag_owner';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
            ],
        ];
    }

    public function getTagOwnerRelationByTagIdAndOwnerTypeAndOwnerId($tagId, $ownerType, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tagId = ? and ownerType = ? and ownerId = ?";

        return $this->db()->fetchAll($sql, [$tagId, $ownerType, $ownerId]) ?: [];
    }

    public function findByOwnerTypeAndOwnerId($ownerType, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ownerType = ? and ownerId = ?";

        return $this->db()->fetchAll($sql, [$ownerType, $ownerId]) ?: [];
    }

    public function findByTagIdsAndOwnerType($tagIds, $ownerType)
    {
        if (empty($tagIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($tagIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE tagId IN ({$marks}) AND ownerType = ?;";

        return $this->db()->fetchAll($sql, array_merge($tagIds, [$ownerType]));
    }

    public function findByOwnerTypeAndOwnerIds($ownerType, $ownerIds)
    {
        if (empty($ownerIds)) {
            return [];
        }
        $marks = str_repeat('?,', count($ownerIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE ownerType = ? and ownerId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge([$ownerType], $ownerIds)) ?: [];
    }

    public function updateByOwnerTypeAndOwnerId($ownerType, $ownerId, $fields)
    {
        $this->db()->update($this->table, ['ownerType' => $ownerType, 'ownerId' => $ownerId], $fields);

        return $this->findByOwnerTypeAndOwnerId($ownerType, $ownerId);
    }

    public function deleteByOwnerTypeAndOwnerId($ownerType, $ownerId)
    {
        return $this->db()->delete($this->table, ['ownerId' => $ownerId, 'ownerType' => $ownerType]);
    }

    public function findDistinctOwnerIdByOwnerTypeAndTagIdsAndExcludeOwnerId($ownerType, array $tagIds, $excludeOwnerId, $count)
    {
        if (!is_numeric($count)) {
            return [];
        }

        $marks = str_repeat('?,', count($tagIds) - 1).'?';
        $sql = "SELECT DISTINCT(ownerId) FROM {$this->table} WHERE ownerType = ? AND tagId IN ({$marks}) AND ownerId <> ? LIMIT {$count}";

        return $this->db()->fetchAll($sql, array_merge([$ownerType], $tagIds, [$excludeOwnerId]));
    }
}
