<?php
namespace Org\OrgBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\WebBundle\Extensions\DataTag\BaseDataTag;

class OrgTreeDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取组织机构的树的数据
     * 默认根据用户所在的组织机构去选择,如果用户未登录,获取跟组织机构下的所有数据
     */
    public function getData(array $arguments)
    {
        $orgCode = isset($arguments['orgCode']) ? $arguments['orgCode'] : null;
        return $this->getOrgService()->findOrgsStartByOrgCode($orgCode);
    }

    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }
}
