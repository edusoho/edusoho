<?php

namespace Codeages\Biz\Framework\Setting\Service;

interface SettingService
{
    public function get($name, $default = null);

    public function set($name, $data);

    public function remove($name);
}
