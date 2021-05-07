<?php

namespace Biz\Sms\Event;

use AppBundle\Common\StringToolkit;
use AppBundle\Component\Notification\WeChatTemplateMessage\MessageSubscribeTemplateUtil;
use Biz\Sms\Service\SmsService;
use Biz\Sms\SmsType;
use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayCenterEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'order.pay.success' => 'onPaySuccess',
        ];
    }

    public function onPaySuccess(Event $event)
    {
        $order = $event->getSubject();
        $targetType = $event->getArgument('targetType');
        $smsType = 'sms_'.$targetType.'_buy_notify';

        $userId = $order['userId'];
        $parameters = [];
        $parameters['order_title'] = $order['title'];
        $parameters['order_title'] = StringToolkit::cutter($parameters['order_title'], 20, 15, 4);

        if ('coin' == $targetType) {
            $parameters['totalPrice'] = $order['amount'].'元';
        } else {
            $parameters['totalPrice'] = $order['totalPrice'].'元';
        }

        if ($this->getSmsService()->isOpen($smsType)) {
            return $this->getSmsService()->smsSend($smsType, [$userId], SmsType::BUY_NOTIFY, $parameters);
        }

        $templateCode = 'coin' == $targetType ? MessageSubscribeTemplateUtil::TEMPLATE_COIN_RECHARGE : MessageSubscribeTemplateUtil::TEMPLATE_PAY_SUCCESS;
        if ($this->getWeChatService()->isSubscribeSmsEnabled($templateCode)) {
            return $this->getWeChatService()->sendSubscribeSms(
                $templateCode,
                [$userId],
                SmsType::BUY_NOTIFY,
                $parameters
            );
        }
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->getBiz()->service('Sms:SmsService');
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->getBiz()->service('WeChat:WeChatService');
    }
}
