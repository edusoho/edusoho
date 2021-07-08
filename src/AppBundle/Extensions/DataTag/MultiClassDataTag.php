<?php

namespace AppBundle\Extensions\DataTag;

use Biz\MultiClass\Service\MultiClassService;

class MultiClassDataTag extends BaseDataTag implements DataTag
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
        return $this->getMultiClassService()->getMultiClassByCourseId($arguments['courseId']);
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->getServiceKernel()->getBiz()->service('MultiClass:MultiClassService');
    }
}
