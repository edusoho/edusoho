<?php

namespace Biz\User\Register\Impl;

use AppBundle\Common\SimpleValidator;
use Biz\User\UserException;

/**
 * 手机注册类
 */
class MobileRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration)
    {
        if (!empty($registration['mobile']) && !SimpleValidator::mobile($registration['mobile'])) {
            throw UserException::MOBILE_INVALID();
        }

        if (!$this->getUserService()->isMobileAvaliable($registration['mobile'])) {
            throw UserException::MOBILE_EXISTED();
        }
    }

    protected function dealDataBeforeSave($registration, $user)
    {
        if (empty($registration['email'])) {
            $user['email'] = $this->getUserService()->generateEmail($registration);
        }

        return $user;
    }
}
