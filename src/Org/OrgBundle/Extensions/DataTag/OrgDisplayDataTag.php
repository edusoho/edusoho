<?php

namespace Org\OrgBundle\Extensions\DataTag;


use Topxia\WebBundle\Extensions\DataTag\BaseDataTag;
use Topxia\WebBundle\Extensions\DataTag\DataTag;

class OrgDisplayDataTag extends BaseDataTag implements DataTag
{

    public function getData(array $arguments)
    {
        if (isset($arguments['id'])) {
          return $this->getOrgService()->geFullOrgNameById($arguments['id']);
        }

        if (isset($arguments['orgCode'])) {
            $org = $this->getOrgService()->getOrgByOrgCode($arguments['orgCode']);
            return  $this->getOrgService()->geFullOrgNameById($org['id']);
        }

        return null;
    }

    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }
}