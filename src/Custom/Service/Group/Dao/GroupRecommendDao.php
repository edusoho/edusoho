<?php

namespace Custom\Service\Group\Dao;

interface GroupRecommendDao
{
    const TABLENAME = 'groups_recommend';

    public function getGroupRecommend($id);

    public function addGroupRecommend($group);

    // public function updateGroupRecommend($id, $fields);

    public function deleteGroupRecommend($id);

    public function getRecommendByGroupId(array $groupIds);
    
	public function getRecommendList($count);


}