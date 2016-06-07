<?php

namespace Topxia\Service\System;

interface SettingService
{
    public function set($name, $value);

    public function setByNamespace($namespace,$name,$value);

    public function get($name, $default = NULL);

    public function delete ($name);

    public function deleteByNamespaceAndName($namespace,$name);
    
}