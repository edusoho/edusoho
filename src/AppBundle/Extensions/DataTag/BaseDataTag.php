<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
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
        if (!empty($magic['enable_org']) && (bool) $magic['enable_org']) {
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

    protected function checkArguments(array $arguments, $requires)
    {
        if (!ArrayToolkit::requireds($arguments, $requires)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
    }

    protected function getCurrentUser()
    {
        return ServiceKernel::instance()->getCurrentUser();
    }

    protected function setting($name, $default = array())
    {
        return ServiceKernel::instance()->createService('System:SettingService')->get($name, $default);
    }

    protected function createService($alias)
    {
        return $this->getServiceKernel()->getBiz()->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->getServiceKernel()->getBiz()->dao($alias);
    }
}
