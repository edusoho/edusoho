<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class VipLevelsDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取一个VIP等级
     *
     * @param  array $arguments 参数
     * @return array 分类
     */
    
    public function getData(array $arguments)
    {
        $levels = $this->getLevelService()->searchLevels( array('enabled' => 1), 0, 100);

    	return $levels;
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }
}
