<?php

namespace Biz\Taxonomy\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TagGroupTagDao extends GeneralDaoInterface
{
    public function findTagRelationsByGroupId($groupId);

    public function findTagRelationsByTagIds($tagIds);

    public function findTagRelationsByTagId($tagId);

    public function deleteByGroupId($groupId);

    public function deleteByGroupIdAndTagId($groupId, $tagId);
}
