<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagGroupTagDao;

class TagGroupTagDaoImpl extends BaseDao implements TagGroupTagDao
{
    protected $table = 'tag_group_tag';

    public function findTagsByGroupId($groupId)
    {
        $that = $this;

        return $this->fetchCached("groupId:{$groupId}", $groupId, function ($groupId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE groupId = ?";
            return $that->getConnection()->fetchAll($sql, array($groupId)) ?: array();
        }

        );
    }
}
