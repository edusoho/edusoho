<?php

namespace MarketingMallBundle\Biz\Mall\Event;

use Biz\System\Service\PaymentSettingService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Client\MarketingMallClient;

class MallEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'setting.school.logo.update' => 'notifySchoolLogo',
            'payment.setting.set' => 'onPaymentSettingSet',
            'setting.wap.update' => 'onWapSettingUpdate',
            'user.delete' => 'onUserDelete',
            //TODO @see MarketingMallBundle\Event\UserEventSubscriber::onUserLock 存在异步事件，看是否移除
            'user.lock' => 'onUserLock',
            'user.unlock' => 'onUserUnLock',
            'user.unbind' => 'onUserUnBind',
        ];
    }

    public function notifySchoolLogo(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $this->getMallClient()->notifyUpdateLogo();
    }

    public function onWapSettingUpdate(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $this->getMallClient()->notifyWapUpdate();
    }

    public function onPaymentSettingSet(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $setting = $this->getPaymentSettingService()->get();
        $this->getMallClient()->setPaymentSetting([
            'enabled' => (bool) $setting['wxpay_enabled'] ?? false,
            'refundEnable' => !empty($setting['wxpay_cert_path']) && !empty($setting['wxpay_key_path']),
        ]);
    }

    public function onUserDelete(Event $event)
    {
        $user = $event->getSubject();
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $this->getMallClient()->deleteUser([
            'id' => $user['id'],
            'username' => $user['nickname'],
        ]);
    }

    public function onUserLock(Event $event)
    {
        $user = $event->getSubject();
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $this->getMallClient()->lockUser([
            'id' => $user['id'],
        ]);
    }

    public function onUserUnLock(Event $event)
    {
        $user = $event->getSubject();
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $this->getMallClient()->unlockUser([
            'id' => $user['id'],
        ]);
    }

    public function onUserUnBind(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $user = $event->getSubject();
        $this->getMallClient()->unbindUser([
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
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->getBiz()->service('Mall:MallService');
    }

    /**
     * @return PaymentSettingService
     */
    protected function getPaymentSettingService()
    {
        return $this->getBiz()->service('System:PaymentSettingService');
    }
}
