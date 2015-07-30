<?php

namespace Topxia\AdminBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as WebBaseController;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

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

    protected function refreshCopyright($info = array())
    {
        $settingService = $this->getServiceKernel()->createService('System.SettingService');

        if (empty($info)) {
            $api = CloudAPIFactory::create('leaf');
            $info = $api->get('/me');
        }

        if (isset($info['copyright'])) {
            if ($info['copyright']) {
                $copyright = $settingService->get('copyright', array());
                if (empty($copyright['owned'])) {
                    $copyright['owned'] = 1;
                    $settingService->set('copyright', $copyright);
                }
            } else {
                $settingService->delete('copyright');
            }
        }
    }

}
