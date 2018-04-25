<?php

namespace AppBundle\Extensions\DataTag;

class VipStatusDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取当前购买是否符合VIP规则.
     *
     * 可传入的参数：
     *      userId 必须 用户id
     *      levelId 必须 会员等级
     *
     * @param array $arguments 参数
     *
     * @return bool vipStatus
     */
    public function getData(array $arguments)
    {
        $result = $this->getVipService()->checkUserInMemberLevel($arguments['userId'], $arguments['levelId']);
        if ($result == 'ok') {
            return 1;
        }

        return 0;
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->getBiz()->service('VipPlugin:Vip:VipService');
    }
}
