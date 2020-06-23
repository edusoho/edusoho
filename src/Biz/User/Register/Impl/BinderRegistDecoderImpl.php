<?php

namespace Biz\User\Register\Impl;

use Biz\System\SettingException;

class BinderRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration)
    {
        $type = $registration['type'];

        $thirdLoginInfo = $this->getSettingService()->get('login_bind', []);
        if ((empty($thirdLoginInfo["{$type}_enabled"]) ||
                empty($thirdLoginInfo["{$type}_key"]) ||
                empty($thirdLoginInfo["{$type}_secret"])) && 'apple' != $type) {
            throw SettingException::OAUTH_CLIENT_TYPE_INVALID();
        }
    }

    protected function dealDataBeforeSave($registration, $user)
    {
        if (!empty($registration['password'])) {
            $user['salt'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user['password'] = $this->getPasswordEncoder()->encodePassword($registration['password'], $user['salt']);
        } else {
            $user['salt'] = '';
            $user['password'] = '';
        }

        if (in_array($registration['type'], ['weixinmob', 'weixinweb'])) {
            $user['type'] = 'weixin';
        }

        return $user;
    }

    protected function dealDataAfterSave($registration, $user)
    {
        $this->getUserService()->bindUser(
            $registration['type'],
            $registration['authid'],
            $user['id'],
            []
        );
    }

    /**
     * return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
