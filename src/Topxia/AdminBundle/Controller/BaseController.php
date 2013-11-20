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
        if (!is_array($disableds) or empty($disableds)) {
            return array();
        }

        return $disableds;
    }

}
