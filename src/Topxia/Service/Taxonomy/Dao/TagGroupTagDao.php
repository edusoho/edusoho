<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagGroupTagDao
{
    public function findTagsByGroupId($groupId);
}
