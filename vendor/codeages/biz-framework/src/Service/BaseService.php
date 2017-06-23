<?php

namespace Codeages\Biz\Framework\Service;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Event\Event;

abstract class BaseService
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function dispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function dispatch($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->dispatcher()->dispatch($eventName, $event);
    }
}
