<?php

namespace Topxia\Service\Group\Dao;

interface GroupDao
{
	public function getGroup($id);

	public function getGroupsByIds($ids);

	public function getGroupByTitle($title);

    public function searchGroups($conditions, $orderBy, $start, $limit);

	public function searchGroupsCount($condtions);

    public function addGroup($group);

    public function updateGroup($id, $fields);

    public function waveGroup($id, $field, $diff);
 
}