<?php

namespace AppBundle\Controller\AdminV2;

use Codeages\Biz\Framework\Event\Event;

class BaseController extends \AppBundle\Controller\BaseController
{
    protected function dispatchEvent($eventName, $subject, $arguments = [])
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }
        $biz = $this->getBiz();

        return $biz['dispatcher']->dispatch($eventName, $event);
    }
}
