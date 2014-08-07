<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class VipLevelsDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取一个VIP等级
     *
     * @param  array $arguments 参数
     * @return array vip等级
     */
    
    public function getData(array $arguments)
    {
        $levels = $this->getLevelService()->searchLevels( array('enabled' => 1), 0, $arguments['count']);

    	return $levels;
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}
