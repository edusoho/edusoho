<?php

namespace AppBundle\Controller\OAuth2;

class OAuthUser implements \Serializable
{
    const SESSION_KEY = 'oauth_user';

    const SESSION_SKIP_KEY = 'oauth_user_green_channel';

    const MOBILE_TYPE = 'mobile';

    const EMAIL_TYPE = 'email';

    const REGISTER_CLOSED = 'closed';

    /**
     * @var string 第三方id
     */
    public $authid;

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

    /**
     * @var bool 是否已绑定
     */
    public $authenticated = false;

    /**
     * @var bool 图形验证码是否已开启
     */
    public $captchaEnabled = false;

    /**
     * @var bool 是否是新用户
     */
    public $isNewAccount = false;

    public function serialize()
    {
        return serialize(array(
            $this->authid,
            $this->type,
            $this->avatar,
            $this->name,
            $this->mode,
            $this->account,
            $this->accountType,
            $this->os,
            $this->authenticated,
            $this->captchaEnabled,
            $this->isNewAccount,
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->authid,
            $this->type,
            $this->avatar,
            $this->name,
            $this->mode,
            $this->account,
            $this->accountType,
            $this->os,
            $this->authenticated,
            $this->captchaEnabled,
            $this->isNewAccount
            ) = $data;
    }

    public function isApp()
    {
        return !empty($this->os);
    }
}
