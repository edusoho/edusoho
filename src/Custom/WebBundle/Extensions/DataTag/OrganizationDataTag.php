<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/22
 * Time: 13:57
 */

namespace Custom\WebBundle\Extensions\DataTag;


use Topxia\WebBundle\Extensions\DataTag\DataTag;

class OrganizationDataTag extends BaseDataTag implements DataTag
{
    /**
     * @param 参数 ID 或者 ALL
     * @return array 学校组织
     */
    public function getData(array $arguments)
    {
        if(!empty($arguments['all'])){
            return $this->getOrganizationService()->findAllOrganizations();
        }

        if(empty($arguments['id'])){
            return array();
        }


        $orgId = $arguments['id'];

        return $this->getOrganizationService()->getOrganization($orgId);

    }

    public function getOrganizationService()
    {
        return $this->createService('Custom:Organization.OrganizationService');
    }

}