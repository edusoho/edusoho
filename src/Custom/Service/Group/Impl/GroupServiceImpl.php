<?php
namespace Custom\Service\Group\Impl;

use Topxia\Service\Group\Impl\GroupServiceImpl as BaseService;
use Custom\Service\Group\GroupService;

class GroupServiceImpl extends BaseService implements GroupService
{
	
	public function recommendGroup($id, $number)
	{
		if (!is_numeric($number)) {
			throw $this->createAccessDeniedException('推荐小组序号只能为数字！');
		}

		$group = $this->getGroupRecommendDao()->addGroupRecommend(array(
			'groupID' => (int)$id,
			'seq' => (int)$number,
			'createdTime' => time(),
		));

		$this->getLogService()->info('group', 'recommend', "推荐课程《{$group['groupID']}》(#{$group['id']}),序号为{$number}");

		return $group;
	}


	public function deleteGroupRecommend($groupId){
		 $this->getGroupRecommendDao()->deleteGroupRecommend($groupId);
		 return true;
	}

	public function getRecommendByGroupId(array $groupIds){
		 return $this->getGroupRecommendDao()->getRecommendByGroupId($groupIds);
	}

    private function getLogService() 
    {
        return $this->createService('System.LogService');
    }
    
    private function getGroupRecommendDao() 
    {
        return $this->createDao('Custom:Group.GroupRecommendDao');
    }

	
}