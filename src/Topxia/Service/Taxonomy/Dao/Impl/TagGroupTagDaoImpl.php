<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagGroupTagDao;

class TagGroupTagDaoImpl extends BaseDao implements TagGroupTagDao
{
    protected $table = 'tag_group_tag';

    public function getTagGroupByGroupId($groupId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE groupId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($groupId));
    }

    public function create($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert tagGroup error.');
        }
        $this->clearCached();
        return $this->getTagGroupByGroupId($this->getConnection()->lastInsertId());

    }

    public function findTagsByGroupId($groupId)
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
        return $this->getTagGroupByGroupId($groupId);
    }

    public function delete($groupId)
    {
        $result = $this->getConnection()->delete($this->table, array('groupId' => $groupId));
        $this->clearCached();
        return $result;
    }
}
