<?php

namespace Codeages\Biz\Framework\Event;

use Codeages\Biz\Framework\Context\Kernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class EventSubscriber implements EventSubscriberInterface
{
    private $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getKernel()
    {
        return $this->kernel;
    }
}
