<?php

namespace MarketingMallBundle\Biz\Mall\Event;

use Biz\System\Service\LoginBindSettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Client\MarketingMallClient;

class MallEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'setting.school.logo.update' => 'notifySchoolLogo',
            'setting.login_bind.set' => 'onLoginBindSettingSet',
            'user.delete' => 'onUserDelete',
            //TODO @see MarketingMallBundle\Event\UserEventSubscriber::onUserLock 存在异步事件，看是否移除
            'user.lock' => 'onUserLock',
            'user.unlock' => 'onUserUnLock',
        ];
    }

    public function notifySchoolLogo(Event $event)
    {
        $client = new MarketingMallClient($this->getBiz());
        $client->notifyUpdateLogo();
    }

    public function onLoginBindSettingSet(Event $event)
    {
        $loginConnect = $this->getLoginBindSettingService()->get();
        //todo 判断是否初始化商城
        $this->getMallClient()->setWechatMobileSetting([
            'appId' => $loginConnect['weixinmob_key'] ?? '',
            'appSecret' => $loginConnect['weixinmob_secret'] ?? '',
            'mpFileCode' => $loginConnect['weixinmob_mp_secret'] ?? '',
        ]);
    }

    public function onUserDelete(Event $event)
    {
        $user = $event->getSubject();
        //todo 判断是否初始化商城
        $this->getMallClient()->deleteUser([
            'id' => $user['id'],
            'username' => $user['nickname'],
        ]);
    }

    public function onUserLock(Event $event)
    {
        $user = $event->getSubject();
        //todo 判断是否初始化商城
        $this->getMallClient()->lockUser([
            'id' => $user['id'],
        ]);
    }

    public function onUserUnLock(Event $event)
    {
        $user = $event->getSubject();
        //todo 判断是否初始化商城
        $this->getMallClient()->unlockUser([
            'id' => $user['id'],
        ]);
    }

    /**
     * @return MarketingMallClient
     */
    public function getMallClient()
    {
        return new MarketingMallClient($this->getBiz());
    }

    /**
     * @return LoginBindSettingService
     */
    protected function getLoginBindSettingService()
    {
        return $this->getBiz()->service('System:LoginBindSettingService');
    }
}
