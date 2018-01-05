<?php

namespace AppBundle\Extensions\DataTag;

class HotGroupDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取最热小组.
     *
     * 可传入的参数：
     *
     *   count 必需 必需 小组数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 最热小组
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('count参数缺失'));
        } else {
            $hotGroups = $this->getGroupService()->searchGroups(array('status' => 'open'), array('memberNum' => 'DESC'), 0, $arguments['count']);
        }

        return $hotGroups;
    }

    private function getGroupService()
    {
        return $this->getServiceKernel()->getBiz()->service('Group:GroupService');
    }
}
