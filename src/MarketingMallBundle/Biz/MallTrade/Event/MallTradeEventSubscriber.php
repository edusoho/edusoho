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
            'unified_payment.trade.paid' => 'onTradePaid',
        ];
    }

    public function onTradePaid(Event $event)
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

        $client = new MarketingMallClient($this->getBiz());
        $client->notifyPaid($params);
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->getBiz()->service('Mall:MallService');
    }
}
