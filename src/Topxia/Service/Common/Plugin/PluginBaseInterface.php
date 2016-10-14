<?php
namespace Topxia\Service\Common\Plugin;

interface PluginBaseInterface
{
    public function getPluginInfo();

    public function register();

    public function boot();

}