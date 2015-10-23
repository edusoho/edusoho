<?php

namespace Topxia\Service\System;

interface SettingService
{
    public function set($name, $value);

    public function get($name, $default = NULL);

    public function delete ($name);
    
}