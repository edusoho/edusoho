<?php
namespace Mooc\WebBundle\Processor\Impl;

use Topxia\MobileBundleV2\Processor\Impl\UserProcessorImpl;

class MoocUserProcessorImpl extends UserProcessorImpl
{
    public function login()
    {
        $keyword = $this->getParam('_username');
        $user = $this->getUserService()->getUserByLoginField($keyword);
        if ($user) {
            $this->setParam('_username', $user['nickname']);
        }
        return parent::login();
    }
}