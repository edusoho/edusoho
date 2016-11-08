<?php
namespace Topxia\Service\Taxonomy;

interface TagGroupTagService
{   
    public function findTagsByGroupId($groupId);

    public function search($conditions, $order, $start, $limit);
}
