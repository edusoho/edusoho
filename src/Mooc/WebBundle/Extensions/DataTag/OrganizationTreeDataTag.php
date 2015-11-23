<?php
namespace Mooc\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class OrganizationTreeDataTag extends BaseDataTag implements DataTag
{
    /**
     * @param array $arguments
     */
    public function getData(array $arguments)
    {
        return $this->getOrganizationService()->getOrganizationTree();
    }

    public function getOrganizationService()
    {
        return $this->createService('Mooc:Organization.OrganizationService');
    }

}
