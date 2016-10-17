<?php

namespace Codeages\PluginBundle\Biz\Service;

interface AppService
{

    public function getApp($id);

    public function findAllPlugins();

    public function installPlugin($code);

    public function removePlugin($code);
}