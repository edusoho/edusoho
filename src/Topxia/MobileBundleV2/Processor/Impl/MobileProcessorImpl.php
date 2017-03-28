<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\MobileProcessor;

class MobileProcessorImpl extends BaseProcessor implements MobileProcessor
{
    public function autoLogin()
    {
        $goto = $this->getParam('goto');
        $user = $this->controller->getUserByToken($this->request);

        if ($user->isLogin()) {
            $this->controller->autoLogin($user);
        }

        return $this->controller->redirectSafely($goto);
    }
}
