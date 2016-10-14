<?php
namespace Topxia\Service\Common\Plugin;

interface PluginInterface
{
    public function getPluginInfo();

    public function registered();

    public function unregistered();

    public function enabled();

    public function disabled();
}