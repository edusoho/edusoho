<?php
namespace Topxia\Service\CardBag\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class CardBagEventSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return array(
            'card.add' => 'onCardAdd'
        );
    }

    public function onCardAdd(ServiceEvent $event)
    {
    	$card = $event->getSubject();
    	$this->getCardBagService()->addCard($card);

    }

    protected function getCardBagService()
    {
        return ServiceKernel::instance()->createService('CardBag.CardBagService');
    }
}