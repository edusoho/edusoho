<?php

namespace Biz\User\Register\Impl;

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
    abstract protected function validateBeforeSave($registration);

    /**
     * 用于继承的方法
     */
    protected function dealDataBeforeSave($registration, $user)
    {
        return $user;
    }

    /**
     * 用于继承的方法
     */
    protected function dealDataAfterSave($registration, $user)
    {
    }

    protected function validate($registration)
    {
        parent::validate($registration);
        if (!empty($register)) {
            $register->validateBeforeSave($registration);
        }
        $this->validateBeforeSave($registration);
    }

    protected function beforeSave($registration, $user = array())
    {
        $user = parent::beforeSave($registration, $user);
        if (!empty($register)) {
            $user = $register->dealDataBeforeSave($registration, $user);
        }

        return $this->dealDataBeforeSave($registration, $user);
    }

    protected function afterSave($registration, $user)
    {
        parent::afterSave($registration, $user);
        if (!empty($register)) {
            $register->dealDataAfterSave($registration, $user);
        }
        $this->dealDataAfterSave($registration, $user);
    }
}
