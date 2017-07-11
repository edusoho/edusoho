<?php

namespace Codeages\PluginBundle\System;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class PluginBase extends Bundle implements PluginInterface
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $directory = $this->getPath().DIRECTORY_SEPARATOR.'Migrations';
        if (is_dir($directory)) {
            $biz['migration.directories'][] = $directory;
        }

        $directory = $this->getPath().DIRECTORY_SEPARATOR.'Biz';
        if (is_dir($directory)) {
            $biz['autoload.aliases'][$this->getName()] = "{$this->getNamespace()}\\Biz";
        }
    }
}
