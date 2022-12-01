<?php

namespace Codeages\Biz\ItemBank;

use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    protected function getValidator()
    {
        $this->addValidatorRule();

        return $this->biz['validator'];
    }

    protected function addValidatorRule()
    {
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }
    /**
     * @param string      $eventName
     * @param Event|mixed $subject
     *
     * @return Event
     */
    protected function dispatchEvent($eventName, $subject, $arguments = [])
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }
}
