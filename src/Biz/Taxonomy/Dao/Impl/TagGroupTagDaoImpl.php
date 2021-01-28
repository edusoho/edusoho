<?php

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\TagGroupTagDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TagGroupTagDaoImpl extends GeneralDaoImpl implements TagGroupTagDao
{
    protected $table = 'tag_group_tag';

    public function findTagRelationsByTagIds($tagIds)
    {
        return $this->findInField('tagId', $tagIds);
    }

    public function findTagRelationsByTagId($tagId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE tagId = ?";

        return $this->db()->fetchAll($sql, array($tagId)) ?: array();
    }

    public function findTagRelationsByGroupId($groupId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE groupId = ?";

        return $this->db()->fetchAll($sql, array($groupId)) ?: array();
    }

    public function deleteByGroupId($groupId)
    {
        $result = $this->db()->delete($this->table, array('groupId' => $groupId));

        return $result;
    }

    public function deleteByGroupIdAndTagId($groupId, $tagId)
    {
        $result = $this->db()->delete($this->table, array('groupId' => $groupId, 'tagId' => $tagId));

        return $result;
    }

    public function declares()
    {
        return array(
        );
    }
}
