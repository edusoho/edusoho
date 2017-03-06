<?php

namespace AppBundle\Extensions\DataTag;

class NavigationDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取导航列表.
     *
     * 可传入的参数：
     *   type     必需 导航类型   ['top':'顶部导航'|'foot':'底部导航']
     *
     * @param array $arguments 参数
     *
     * @return array 导航列表
     */
    public function getData(array $arguments)
    {
        return $this->getNavigationService()->getNavigationsListByType($arguments['type']);
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content:NavigationService');
    }
}
