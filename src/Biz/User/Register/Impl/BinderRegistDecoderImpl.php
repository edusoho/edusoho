<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BinderRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration, $type)
    {
        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());
        if (empty($thirdLoginInfo["{$type}_enable"]) || 
                empty($thirdLoginInfo["{$type}_key"]) || 
                empty($thirdLoginInfo["{$type}_secret"])) {
            throw new InvalidArgumentException('Invalid binder type');
        }
    }

    protected function dealDataBeforeSave($registration, $type, $user)
    {
        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());
        if (!empty($thirdLoginInfo["{$type}_set_fill_account"])) {
            $user['salt'] = '';
            $user['password'] = '';
            $user['setup'] = 1;
        }

        return $user;
    }

    protected function dealDataAfterSave($registration, $type, $user)
    {
        $this->getUserService()->bindUser($type, $registration['token']['userId'], $user['id'], $registration['token']);
    }

    /**
     * return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
