<?php
namespace Topxia\Service\Common\Plugin;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class PluginBaseBundle extends Bundle implements PluginInterface
{
    abstract public function getPluginInfo();

    public function registered()
    {

    }

    public function unregistered()
    {

    }

    public function enabled()
    {

    }

    public function disabled()
    {

    }
}