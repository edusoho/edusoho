<?php

namespace AppBundle\Extensions\DataTag;

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
        return $this->createService('Org:OrgService');
    }
}
