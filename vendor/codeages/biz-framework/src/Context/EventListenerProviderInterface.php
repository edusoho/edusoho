<?php

namespace Codeages\Biz\Framework\Context;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pimple\Container;

interface EventListenerProviderInterface
{
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher);
}
