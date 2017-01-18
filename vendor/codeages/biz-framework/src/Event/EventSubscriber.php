<?php

namespace Codeages\Biz\Framework\Event;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return Biz
     */
    public function getBiz()
    {
        return $this->biz;
    }
}
