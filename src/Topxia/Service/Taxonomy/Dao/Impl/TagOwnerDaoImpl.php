<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagOwnerDao;

class TagOwnerDaoImpl extends BaseDao implements TagOwnerDao
{
    protected $table = 'tag_owner';

    public function getTagOwnerRelation($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: array();
    }

    public function findTagOwnerRelationsByOwner(array $owner)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ownerId = ? and ownerType = ?";
        return $this->getConnection()->fetchAll($sql, array($owner['ownerId'], $owner['ownerType'])) ?: array();
    }

    public function findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, $ownerType)
    {
        if (empty($tagIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($tagIds) - 1).'?';

        $sql   = "SELECT * FROM {$this->table} WHERE tagId IN ({$marks}) AND ownerType = '{$ownerType}';";

        return $this->getConnection()->fetchAll($sql, $tagIds);
    }

    public function addTagOwnerRelation($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }
        $this->clearCached();
        return $this->getTagOwnerRelation($this->getConnection()->lastInsertId());

    }

    public function updateTagOwnerRelationByOwner(array $owner, $fields)
    {
        $this->getConnection()->update($this->table, array('ownerType' => $owner['ownerType'], 'ownerId' => $owner['ownerId']), $fields);
        $this->clearCached();
        return $this->findTagOwnerRelationsByOwner($owner);
    }

    public function deleteTagOwnerRelationByOwner(array $owner)
    {
        $result = $this->getConnection()->delete($this->table, array('ownerId' => $owner['ownerId'], 'ownerType' => $owner['ownerType']));
        $this->clearCached();
        return $result;
    }
}
