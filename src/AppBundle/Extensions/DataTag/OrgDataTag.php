<?php

namespace AppBundle\Extensions\DataTag;

class OrgDataTag extends BaseDataTag implements DataTag
{
    /**
     * 根据Id 或者orgCode 获取组织机构.
     */
    public function getData(array $arguments)
    {
        if (isset($arguments['id'])) {
            return $this->getOrgService()->getOrg($arguments['id']);
        }

        if (isset($arguments['orgCode'])) {
            return $this->getOrgService()->getOrgByOrgCode($arguments['orgCode']);
        }

        return null;
    }

    public function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }
}
