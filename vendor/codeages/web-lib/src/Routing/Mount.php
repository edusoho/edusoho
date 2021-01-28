<?php

namespace Codeages\Weblib\Routing;

interface Mount
{
    public function mount(RoutingProvider $provider);
}
