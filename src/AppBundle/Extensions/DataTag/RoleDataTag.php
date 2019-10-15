<?php

namespace AppBundle\Extensions\DataTag;

class RoleDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取公告列表.
     *
     * 可传入的参数：
     *   code    必需
     *
     * @param array $arguments 参数
     */
    public function getData(array $arguments)
    {
        $this->checkCode($arguments);

        return $this->getRoleService()->getRoleByCode($arguments['code']);
    }

    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    protected function checkCode(array $arguments)
    {
        if (empty($arguments['code'])) {
            throw new \InvalidArgumentException('code参数缺失');
        }
    }
}
