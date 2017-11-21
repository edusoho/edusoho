<?php

namespace AppBundle\Controller\OAuth2;

class OauthUser implements \Serializable
{
    /**
     * @var string 第三方id
     */
    public $id;

    /**
     * @var string 第三方登录类型
     */
    public $type;

    /*
     * @var string 第三方头像地址
     */
    public $avatar;

    /**
     * @var string 第三方昵称
     */
    public $name;

    /**
     * @var string 当前网站注册模式
     */
    public $mode;

    /**
     * @var string 需要绑定的账号
     */
    public $account;

    /**
     * @var string 账号类型
     */
    public $accountType;

    /*
     * @var string 操作系统
     */
    public $os = '';

    public $authenticated = false;

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->type,
            $this->avatar,
            $this->name,
            $this->mode,
            $this->account,
            $this->accountType,
            $this->os,
            $this->authenticated,
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->id,
            $this->type,
            $this->avatar,
            $this->name,
            $this->mode,
            $this->account,
            $this->accountType,
            $this->os,
            $this->authenticated
            ) = $data;
    }

    public function isApp()
    {
        return !empty($this->os);
    }
}
