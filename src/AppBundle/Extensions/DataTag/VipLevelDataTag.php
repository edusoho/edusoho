<?php

namespace AppBundle\Extensions\DataTag;

class VipLevelDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个VIP等级.
     *
     * @param  id 会员等级id
     *
     * @return level vip等级
     */
    public function getData(array $arguments)
    {
        $level = $this->getLevelService()->getLevel($arguments['id']);

        return $level;
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('VipPlugin:Vip:LevelService');
    }
}
