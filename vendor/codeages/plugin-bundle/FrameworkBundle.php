<?php

namespace Codeages\PluginBundle;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle as BaseFrameworkBundle;

class FrameworkBundle extends BaseFrameworkBundle
{
    public function boot()
    {
    }

    public function getNamespace()
    {
        return 'Symfony\Bundle\FrameworkBundle';
    }

    public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionClass('Symfony\Bundle\FrameworkBundle\FrameworkBundle');
            $this->path = dirname($reflected->getFileName());
        }

        return $this->path;
    }

    protected function getContainerExtensionClass()
    {
        return  __NAMESPACE__.'\\DependencyInjection\\FrameworkExtension';
    }
}
