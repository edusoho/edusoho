<?php

namespace Biz\System\Service;

interface LoginBindSettingService
{
    public function get($default = array());

    public function set($value);
}