<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/22
 * Time: 13:39
 */

namespace Custom\WebBundle\Extensions\DataTag;



use Topxia\WebBundle\Extensions\DataTag\DataTag;

class SchoolOrganizationTreeDataTag extends BaseDataTag implements DataTag
{
    /**
     * @param array $arguments
     */
    public function getData(array $arguments)
    {
        return $this->getSchoolService()->getSchoolOrganizationTree();
    }

    public function getSchoolService()
    {
        return $this->createService('Custom:School.SchoolService');
    }

}