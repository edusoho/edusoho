<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class MobileRegistDecoderImpl extends RegistDecoder
{
    public function validateBeforeSave($registration, $type)
    {
        $thirdLoginInfo = $this->getSettingService()->get('login_bind', array());
        if (empty($thirdLoginInfo["{$type}_set_fill_account"]) && $thirdLoginInfo["{$type}_set_fill_account"]) {
            throw new InvalidArgumentException('Invalid binder type');
        }
    }

    protected function dealDataBeforeSave($registration, $type, $user)
    {
        $user['salt'] = '';
        $user['password'] = '';
        $user['setup'] = 1;

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
        return $this->biz->dao('System:SettingService');
    }
}
