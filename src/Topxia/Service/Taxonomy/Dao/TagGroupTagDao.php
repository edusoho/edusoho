<?php
namespace Topxia\Service\Taxonomy\Dao;

interface TagGroupTagDao
{
    public function findTagsByGroupId($groupId);

    public function search($conditions, $order, $start, $limit);
}
