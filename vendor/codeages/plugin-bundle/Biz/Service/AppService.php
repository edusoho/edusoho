<?php

namespace Codeages\PluginBundle\Biz\Service;

interface AppService
{
    public function getApp($id);

    public function getAppByCode($code);

    public function findAllPlugins();

    public function registerPlugin($plugin);

    public function removePlugin($code);
}
