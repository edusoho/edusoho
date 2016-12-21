<?php


namespace Biz\Group\Service;


interface GroupService
{
    public function countMembers($conditions);

    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function getGroupsByIds($ids);
}

