<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/22
 * Time: 13:57
 */

namespace Custom\WebBundle\Extensions\DataTag;


use Topxia\WebBundle\Extensions\DataTag\DataTag;

class SchoolOrganizationDataTag extends BaseDataTag implements DataTag
{
    /**
     * @param 参数 ID 或者 ALL
     * @return array 学校组织
     */
    public function getData(array $arguments)
    {
        if(!empty($arguments['all'])){
            return $this->getSchoolService()->findAllSchoolOrganization();
        }

        if(empty($arguments['id'])){
            return array();
        }


        $orgId = $arguments['id'];

        return $this->getSchoolService()->getSchoolOrganization($orgId);

    }

    public function getSchoolService()
    {
        return $this->createService('Custom:School.SchoolService');
    }

}