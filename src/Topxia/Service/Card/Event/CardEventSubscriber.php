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
        $this->getCardService()->updateCardByCardIdAndType($coupon['id'],'coupon',$card);

    }

    public function onMoneyCardAdd(ServiceEvent $event)
    {   
        $user = $this->getCurrentUser();
        $moneyCard = $event->getSubject();
        $card = array(
            'cardId' => $moneyCard['id'],
            'cardType' => 'moneyCard',
            'deadline' => strtotime($moneyCard['deadline']),
            'userId' => $user['id'],
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
        $this->getCardService()->updateCardByCardIdAndType($moneyCard['id'],'moneyCard',$card);
    }

    protected function getCurrentUser()
    {
        return ServiceKernel::instance()->createService('User.UserService')->getCurrentUser();
    }

    protected function getCardService()
    {
        return ServiceKernel::instance()->createService('Card.CardService');
    }
}