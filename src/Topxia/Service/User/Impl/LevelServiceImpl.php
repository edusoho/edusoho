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

	public function findLevelsBySeq($seq, $start, $limit)
	{
		$levels = $this->getLevelDao()->findLevelsBySeq($seq, $start, $limit);
		return $levels;
	}

	public function searchLevelsCount($conditions)
	{	
	    return $this->getLevelDao()->searchLevelsCount($conditions);
	}

	public function generateNextSeq()
	{	
		return $this->searchLevelsCount()+1;
	}

	public function createLevel($level)
	{	

	    $level['createdTime'] = time();
	    @$level['seq'] = $this->generateNextSeq();
	    $level = $this->getLevelDao()->createLevel($level);
	    $this->getLogService()->info('level', 'create', "添加会员类型{$level['name']}(#{$level['id']})", $level);

	    return $level;
	}

	public function searchLevels($conditions, $start, $limit)
	{
	    $levels = $this->getLevelDao()->searchLevels($conditions, $start, $limit);
	    return $levels;
	}


	public function updateLevel($id,$fields)
	{
	    $level = $this->getLevelDao()->updateLevel($id, $fields);
	    $this->getLogService()->info('level', 'update', "编辑会员类型{$level['name']}(#{$level['id']})", $level);
	    return $level;
	}

	public function sortLevels(array $ids)
	{	
	    $seq = 0;
	    foreach ($ids as $itemId) {
            $seq ++;
            $item = $this->getLevel($itemId);
            $fields = array('seq' => $seq);
            if ($fields['seq'] != $item['seq']) {
                $this->updateLevel($item['id'], $fields);
	        }
	    }
	}

    public function onLevel($id)
    {
        $level = $this->getLevel($id);
        if (empty($level)) {
            throw $this->createServiceException('会员类型不存在，开启失败！');
        }
        $this->getLevelDao()->updateLevel($level['id'], array('enabled' => 1));

        $this->getLogService()->info('level', 'on', "会员类型{$level['name']}(#{$level['id']})允许加入会员", $level);

        return true;
    }

    public function offLevel($id)
    {
        $level = $this->getLevel($id);
        if (empty($level)) {
            throw $this->createServiceException('会员等级不存在，关闭失败！');
        }
        $this->getLevelDao()->updateLevel($level['id'], array('enabled' => 0));

        $this->getLogService()->info('level', 'off', "会员类型{$level['name']}(#{$level['id']})禁止加入会员", $level);

        return true;
    }

	public function deleteLevel($id)
	{
	    $level = $this->getLevel($id);
	    $this->getLogService()->info('level', 'delete', "删除用户类型{$level['name']}(#{$level['id']})", $level);
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