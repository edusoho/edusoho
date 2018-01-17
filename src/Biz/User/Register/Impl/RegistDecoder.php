<?php

namespace Biz\User\Register\Impl;

abstract class RegistDecoder extends BaseRegister
{
    private $register;

    public function setRegister(BaseRegister $register)
    {
        $this->register = $register;
    }

    public function clearRegister()
    {
        $this->register = null;
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
        if (!empty($this->register)) {
            $this->register->validateBeforeSave($registration);
        }
        $this->validateBeforeSave($registration);
    }

    protected function beforeSave($registration, $user = array())
    {
        list($user, $registration) = parent::beforeSave($registration, $user);
        if (!empty($this->register)) {
            $user = $this->register->dealDataBeforeSave($registration, $user);
        }

        return array($this->dealDataBeforeSave($registration, $user), $registration);
    }

    protected function afterSave($registration, $user)
    {
        parent::afterSave($registration, $user);
        if (!empty($this->register)) {
            $this->register->dealDataAfterSave($registration, $user);
        }
        $this->dealDataAfterSave($registration, $user);
    }
}
