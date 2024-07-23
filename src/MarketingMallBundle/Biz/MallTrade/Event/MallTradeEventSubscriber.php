<?php

namespace MarketingMallBundle\Biz\MallTrade\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Biz\MallWechatNotification\Service\MallWechatNotificationService;
use MarketingMallBundle\Client\MarketingMallClient;

class MallTradeEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'unified_payment.trade.receive_notified' => 'onReceiveNotified',
            'unified_payment.trade.closed' => 'onTradeClosed',
        ];
    }

    public function onReceiveNotified(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $trade = $event->getSubject();
        $params = [
            'orderSn' => $trade['orderSn'],
            'tradeSn' => $trade['tradeSn'],
            'status' => $trade['status']
        ];

        $this->getMallClient()->notifyPaid($params);
    }

    public function onTradeClosed(Event $event)
    {
        if (!$this->getMallService()->isInit()) {
            return;
        }
        $trade = $event->getSubject();
        $params = [
            'orderSn' => $trade['orderSn'],
        ];

        $this->getMallClient()->notifyTradeClosed($params);
    }

    protected function getMallClient()
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
}
