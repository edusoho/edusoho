<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Service\Common\ServiceKernel;

abstract class BaseDataTag
{

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function fillOrgCode($conditions)
    {
        $magic = $this->setting('magic');
        if (!empty($magic['enable_org']) && (bool)$magic['enable_org']) {
            if (!isset($conditions['orgCode'])) {
                $conditions['likeOrgCode'] = $this->getCurrentUser()->getSelectOrgCode();
            } else {
                $conditions['likeOrgCode'] = $conditions['orgCode'];
                unset($conditions['orgCode']);
            }
        } else {
            if (isset($conditions['orgCode'])) {
                unset($conditions['orgCode']);
            }
        }

        return $conditions;
    }

    protected function getCurrentUser()
    {
        return $this->getServiceKernel()->createService('User.UserService')->getCurrentUser();
    }

    protected function setting($name, $default = null)
    {
        return $this->getServiceKernel()->createService('System.SettingService')->get($name);
    }

}
