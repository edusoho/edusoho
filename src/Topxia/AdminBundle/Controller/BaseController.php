<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as WebBaseController;

class BaseController extends WebBaseController
{
    protected function getDisabledFeatures()
    {
        if (!$this->container->hasParameter('disabled_features')) {
            return array();
        }

        $disableds = $this->container->getParameter('disabled_features');

        if (!is_array($disableds) || empty($disableds)) {
            return array();
        }

        return $disableds;
    }
    /** 
     * [getSelectOrgCode 获取当前用的选择的组织机构编码]
     * @return [String] [orgcCode]
     */
    protected function getSelectOrgCode(){
        $enableOrg = $this->setting('magic.enable_org');

        if($enableOrg){
           return $this->getCurrentUser()->getSelectOrgCode();   
        }
    }
}
