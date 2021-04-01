<?php

namespace Biz\User\Register\Impl;

use AppBundle\Common\SimpleValidator;
use Biz\User\UserException;

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

    public function register($registration)
    {
        if (empty($registration['type'])) {
            $registration['type'] = 'default';
        }

        list($user, $registration) = $this->createUser($registration);
        $this->createUserProfile($registration, $user);
        $this->afterSave($registration, $user);

        return [$user, $this->createPerInviteUser($registration, $user['id'])];
    }
}
