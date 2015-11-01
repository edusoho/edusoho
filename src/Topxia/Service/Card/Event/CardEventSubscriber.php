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
            'card.add' => 'onCardAdd'
        );
    }

    public function onCardAdd(ServiceEvent $event)
    {
    	$fields = $event->getSubject();
        $card = ArrayToolkit::parts($fields, array('cardType', 'cardId', 'deadline', 'userId'));
    	$this->getCardService()->addCard($card);

    }


    protected function getCardService()
    {
        return ServiceKernel::instance()->createService('Card.CardService');
    }
}