<?php

namespace Custom\Service\Group;

interface GroupService
{
	
	public function recommendGroup($id,$number);

	public function deleteGroupRecommend($groupId);

	public function getRecommendByGroupId(array $groupIds);
   
}