<?php

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\TagOwnerDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TagOwnerDaoImpl extends AdvancedDaoImpl implements TagOwnerDao
{
    protected $table = 'tag_owner';

    public function declares()
    {
        return array();
    }

    public function getTagOwnerRelationByTagIdAndOwnerTypeAndOwnerId($tagId, $ownerType, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tagId = ? and ownerType = ? and ownerId = ?";

        return $this->db()->fetchAll($sql, array($tagId, $ownerType, $ownerId)) ?: array();
    }

    public function findByOwnerTypeAndOwnerId($ownerType, $ownerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ownerType = ? and ownerId = ?";

        return $this->db()->fetchAll($sql, array($ownerType, $ownerId)) ?: array();
    }

    public function findByTagIdsAndOwnerType($tagIds, $ownerType)
    {
        if (empty($tagIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($tagIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE tagId IN ({$marks}) AND ownerType = ?;";

        return $this->db()->fetchAll($sql, array_merge($tagIds, array($ownerType)));
    }

    public function findByOwnerTypeAndOwnerIds($ownerType, $ownerIds)
    {
        if (empty($ownerIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($ownerIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE ownerType = ? and ownerId IN ({$marks});";

        return $this->db()->fetchAll($sql, array_merge(array($ownerType), $ownerIds)) ?: array();
    }

    public function updateByOwnerTypeAndOwnerId($ownerType, $ownerId, $fields)
    {
        $this->db()->update($this->table, array('ownerType' => $ownerType, 'ownerId' => $ownerId), $fields);

        return $this->findByOwnerTypeAndOwnerId($ownerType, $ownerId);
    }

    public function deleteByOwnerTypeAndOwnerId($ownerType, $ownerId)
    {
        $result = $this->db()->delete($this->table, array('ownerId' => $ownerId, 'ownerType' => $ownerType));

        return $result;
    }
}
