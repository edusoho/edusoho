<?php

namespace AppBundle\Extensions\DataTag;

class GroupDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个小组.
     *
     * 可传入的参数：
     *   groupId 必需 小组ID
     *
     * @param array $arguments 参数
     *
     * @return array 小组
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['groupId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('groupId参数缺失'));
        } else {
            $group = $this->getGroupService()->getGroup($arguments['groupId']);
        }

        return $group;
    }

    private function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group:GroupService');
    }
}
