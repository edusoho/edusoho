<?php

namespace AppBundle\Extensions\DataTag;

class VipLevelDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个VIP等级.
     *
     * @param  $arguments array (id  => 会员等级id)
     *
     * @return array vip level
     */
    public function getData(array $arguments)
    {
        return $this->getLevelService()->getLevel($arguments['id']);
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->getBiz()->service('VipPlugin:Vip:LevelService');
    }
}
