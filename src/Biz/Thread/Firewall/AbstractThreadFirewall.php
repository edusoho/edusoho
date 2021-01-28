<?php

namespace Biz\Thread\Firewall;

use UnderflowException;

abstract class AbstractThreadFirewall
{
    public function accessThreadRead($thread)
    {
        throw new UnderflowException('Method accessThreadRead is not implement');
    }

    public function accessThreadDelete($thread)
    {
        throw new UnderflowException('Method accessThreadDelete is not implement');
    }

    public function accessThreadUpdate($thread)
    {
        throw new UnderflowException('Method accessThreadUpdate is not implement');
    }

    public function accessThreadSticky($thread)
    {
        throw new UnderflowException('Method accessThreadSticky is not implement');
    }

    public function accessThreadNice($thread)
    {
        throw new UnderflowException('Method accessThreadNice is not implement');
    }

    public function accessPostCreate($post)
    {
        throw new UnderflowException('Method accessPostCreate is not implement');
    }

    public function accessPostUpdate($post)
    {
        throw new UnderflowException('Method accessPostUpdate is not implement');
    }

    public function accessPostDelete($post)
    {
        throw new UnderflowException('Method accessPostDelete is not implement');
    }
}
