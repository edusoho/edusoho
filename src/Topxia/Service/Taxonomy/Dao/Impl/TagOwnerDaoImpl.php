<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagOwnerDao;

class TagOwnerDaoImpl extends BaseDao implements TagOwnerDao
{
    protected $table = 'tag_owner';

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: array();
    }

    public function getTagOwnerRelationByTagIdAndOwnerTypeAndOwnerId($tagId, $ownerType, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tagId = ? and ownerType = ? and ownerId = ?";
        return $this->getConnection()->fetchAll($sql, array($tagId, $ownerType, $ownerId)) ?: array();
    }

    public function findByOwnerTypeAndOwnerId($ownerType, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ownerType = ? and ownerId = ?";
        return $this->getConnection()->fetchAll($sql, array($ownerType, $ownerId)) ?: array();
    }

    public function findByOwnerTypeAndOwnerIds($ownerType, $ownerIds)
    {
        if (empty($ownerIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($ownerIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE ownerType = '{$ownerType}' and ownerId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ownerIds) ?: array();
    }

    public function findByTagIdsAndOwnerType($tagIds, $ownerType)
    {
        if (empty($tagIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($tagIds) - 1).'?';

        $sql   = "SELECT * FROM {$this->table} WHERE tagId IN ({$marks}) AND ownerType = '{$ownerType}';";

        return $this->getConnection()->fetchAll($sql, $tagIds);
    }

    public function add($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }
        $this->clearCached();
        return $this->get($this->getConnection()->lastInsertId());

    }

    public function updateByOwnerTypeAndOwnerId($ownerType, $ownerId, $fields)
    {
        $this->getConnection()->update($this->table, array('ownerType' => $ownerType, 'ownerId' => $ownerId), $fields);
        $this->clearCached();
        return $this->findByOwnerTypeAndOwnerId($ownerType, $ownerId);
    }

    public function deleteByOwnerTypeAndOwnerId($ownerType, $ownerId)
    {
        $result = $this->getConnection()->delete($this->table, array('ownerId' => $ownerId, 'ownerType' => $ownerType));
        $this->clearCached();
        return $result;
    }
}
