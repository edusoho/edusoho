<?php
namespace Topxia\Service\Card\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class CardEventSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return array(
            'coupon.receive' => 'onCouponAdd',
            'coupon.use' => 'onCouponUse',
            'moneyCard.receive' => 'onMoneyCardAdd',
            'moneyCard.use' => 'onMoneyCardUse',
        );
    }

    public function onCouponAdd(ServiceEvent $event)
    {
    	$coupon = $event->getSubject();
        $card = array(
            'cardId' => $coupon['id'],
            'cardType' => 'coupon',
            'deadline' => $coupon['deadline'],
            'userId' => $coupon['userId']
        );
    	$this->getCardService()->addCard($card);

    }

    public function onCouponUse(ServiceEvent $event)
    {
        $coupon = $event->getSubject();
        $card = array(
            'cardId' => $coupon['id'],
            'cardType' => 'coupon',
            'status' => 'used',
            'useTime' => $coupon['orderTime']
        );
        $this->getCardService()->updateCard($coupon['id'],'coupon',$card);

    }

    public function onMoneyCardAdd(ServiceEvent $event)
    {
        $moneyCard = $event->getSubject();
        $card = array(
            'cardId' => $moneyCard['id'],
            'cardType' => 'moneyCard',
            'deadline' => strtotime($moneyCard['deadline']),
            'userId' => $moneyCard['userId'],
        );
        $this->getCardService()->addCard($card);

    }

    public function onMoneyCardUse(ServiceEvent $event)
    {
        $moneyCard = $event->getSubject();
        $card = array(
            'cardId' => $moneyCard['id'],
            'cardType' => 'moneyCard',
            'status' => 'used',
            'useTime' => $moneyCard['rechargeTime'],
        );
        $this->getCardService()->updateCard($moneyCard['id'],'moneyCard',$card);
    }

    protected function getCardService()
    {
        return ServiceKernel::instance()->createService('Card.CardService');
    }
}