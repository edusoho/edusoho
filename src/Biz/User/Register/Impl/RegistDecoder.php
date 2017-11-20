<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Context\Biz;

abstract class RegistDecoder extends BaseRegister
{
    private $register;

    public function setRegister(BaseRegister $register)
    {
        $this->register = $register;
    }

    /**
     * 用于继承的方法
     */
    abstract protected function validateBeforeSave($registration, $type);

    /**
     * 用于继承的方法
     */
    protected function dealDataBeforeSave($registration, $type, $user)
    {
        return $user;
    }

    /**
     * 用于继承的方法
     */
    protected function dealDataAfterSave($registration, $type, $user)
    {
    }

    protected function validate($registration, $type)
    {
        parent::validate($registration, $type);
        if (!empty($register)) {
            $register->validateBeforeSave($registration);
        }
        $this->validateBeforeSave($registration, $type);
    }

    protected function beforeSave($registration, $type, $user = array())
    {
        $user = parent::beforeSave($registration, $type, $user);
        if (!empty($register)) {
            $user = $register->dealDataBeforeSave($registration, $type, $user);
        }

        return $this->dealDataBeforeSave($registration, $type, $user);
    }

    protected function afterSave($registration, $type, $user)
    {
        parent::afterSave($registration, $type, $user);
        if (!empty($register)) {
            $register->dealDataAfterSave($registration, $type, $user);
        }
        $this->dealDataAfterSave($registration, $type, $user);
    }
}
