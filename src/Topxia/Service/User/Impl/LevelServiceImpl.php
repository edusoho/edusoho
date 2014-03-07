<?php
namespace Topxia\Service\User\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\User\LevelService;

class LevelServiceImpl extends BaseService implements LevelService
{

	public function getLevel($id)
	{
	    return $this->getLevelDao()->getLevel($id);
	}

	public function getLevelByName($name)
	{
	    return $this->getLevelDao()->getLevelByName($name);
	}

	public function isLevelNameAvailable($name, $exclude=null)
	{
	    if (empty($name)) {
	        return false;
	    }

	    if ($name == $exclude) {
	        return true;
	    }

	    $level = $this->getLevelByName($name);

	    return $level ? false : true;
	}

	public function searchLevelsCount($conditions)
	{
	    return $this->getLevelDao()->searchLevelsCount($conditions);
	}

	public function createLevel($level)
	{	
	    $level['createdTime'] = time();
	    @$level['seq'] = $this->searchLevelsCount()+1;
	    $level = $this->getLevelDao()->createLevel($level);

	    $this->getLogService()->info('level', 'create', "添加会员等级{$level['name']}(#{$level['id']})");

	    return $level;
	}

	public function searchLevels($conditions, $start, $limit)
	{
	    $levels = $this->getLevelDao()->searchLevels($conditions, $start, $limit);
	    return $levels;
	}


	public function updateLevel($id,$fields)
	{
	    $level = $this->getLevelDao()->updateLevel($id,$fields);
	    $this->getLogService()->info('level', 'update', "编辑会员等级{$level['name']}(#{$level['id']})");
	    return $level;
	}

	public function sortLevels(array $ids)
	{   
	    $levelId  = 0;
	    foreach ($ids as $itemId) {
	        list(, $type) = explode("-",$itemId);
	            $levelId ++;
	            $item = $this->getLevel($type);
	            $fields = array('seq' => $levelId);
	            if ($fields['seq'] != $item['seq']) {
	                $this->updateLevel($item['id'], $fields);
	        }
	    }
	}

	public function deleteLevel($id)
	{
	    $level = $this->getLevel($id);
	    $this->getLogService()->info('level', 'delete', "删除用户等级{$level['name']}(#{$level['id']})");
	    return $this->getLevelDao()->deleteLevel($id);
	}

    private function getLevelDao()
    {
        return $this->createDao('User.LevelDao');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

}