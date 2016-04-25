<?php
namespace Topxia\Service\Card\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class EventSubscriber implements EventSubscriberInterface
{

	public static function getSubscribedEvents()
    {
    	return array(
    		'coupon.use' => 'onCouponUsed',
    	);
    }

    public function onCouponUsed(ServiceEvent $event)
    {
    	$coupon = $event->getSubject();
		$card = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

        if (!empty($card)) {
            $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', array(
                'status'  => 'used',
                'useTime' => $coupon['orderTime']
            ));
        }
    }

    private function getCardService()
    {
        return ServiceKernel::instance()->createService('Card.CardService');
    }

}