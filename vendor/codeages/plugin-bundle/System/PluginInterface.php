<?php

namespace Codeages\PluginBundle\System;

interface PluginInterface
{
    public function boot();

    public function shutdown();
}
