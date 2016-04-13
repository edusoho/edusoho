<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class NavigationsTreeDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取导航列表
     *
     * @param  array $arguments     参数
     * @return array 导航列表
     */
    public function getData(array $arguments)
    {
        return $this->getNavigationService()->getOpenedNavigationsTreeByType('top');
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

}
